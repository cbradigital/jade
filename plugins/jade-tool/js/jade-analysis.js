var jade = {};
jade.initalLoading = false;
jade.jobData = {};
jade.analysisData = {};
jade.expressionFields = {};
jade.sectionFields = {};
jade.printpdf = false;
jade.title = "";

jade.expressionTranslation = {
  younger: "J&uuml;nger",
  elder: "&Auml;lter",
  male: "M&auml;nnlich",
  female: "Weiblich",
};

String.prototype.stripSlashes = function () {
  return this.replace(/\\(.)/gm, "$1");
};

jQuery(function () {
  console.log("JADE ANALYSIS Loaded");
  jQuery.noConflict();

  var jobId = jQuery("#job_id").val();
  var versionId = jQuery("#version_id").val();
  var nonce = jQuery("#nonce").val();
  jade.printpdf = jQuery("#printpdf").val() == "1";
  console.log("PDF Mode: " + jade.printpdf);

  var url = "/wp-json/jade-tool/v1/job?";

  if (jobId) url += "id=" + jobId;
  if (versionId) url += "version_id=" + versionId;

  jade.preparePrintPDF();

  jQuery.ajax({
    type: "GET",
    url: url,
    data: {},
    beforeSend: function (xhr) {
      xhr.setRequestHeader("X-WP-Nonce", nonce);
    },
    success: function (data) {
      console.log(data);

      jade.jobData = data.job_data;
      jade.analysisData = data.analysis_data;
      jade.expressionFields = data.expression_fields;
      jade.sectionFields = data.section_fields;

      initWithData();
      jade.initalLoading = true;

      if (jade.printpdf) jade.printPDF();
    },
  });

  if (jobId) {
    jQuery('[name="jade-analysis"]').click(function (e) {
      e.preventDefault();
      if (!jade.initalLoading) {
        console.log("Data not loaded jey!");
      }

      jQuery("#jade-form").submit();
    });
  } else {
    jQuery('[name="jade-analysis"]').hide();
  }

  jQuery("[btn-pdf-download]").click((e) => {
    e.preventDefault();
    jade.printPDF();
  });
});

function initWithData() {
  // job data
  for (var key in jade.jobData) {
    // Setup Hightlight & Load Data
    jade.jobData[key] = jade.jobData[key].replaceAll('\\"', '"');
    jQuery("#" + key + "-highlight").val(jade.jobData[key]);

    console.log(jade.jobData[key]);

    // get the title
    if (key == "jade-title") jade.title = jade.jobData[key];

    // Load Data
    jQuery("#" + key).val(jade.jobData[key]);
  }

  // Analysis - Overall
  for (var e in jade.expressionFields) {
    var expr = jade.expressionFields[e];
    var german = jade.expressionTranslation[expr];
    var value = parseFloat(jade.analysisData["overall"][expr]);
    jQuery("#overall-" + expr).html(german + ":&nbsp;" + value);

    let offsetSVGSlider = 10.0;
    let maxValue = 30.0;
    let maxSVGSlider = 200.0;

    // Check Boundries
    value = Math.max(value, 0);
    value = Math.min(value, 30);

    // Transform to slider
    value = (value / maxValue) * maxSVGSlider;
    value += offsetSVGSlider;

    jQuery("#overall-" + expr + "-slider ellipse").attr("cx", value);
  }

  // overall-male-slider

  // Analysis - Sections
  for (var s in jade.sectionFields) {
    var section = jade.sectionFields[s];
    var sectionData = jade.analysisData[section];
    var words = [];

    // Numbers
    var p = jQuery("#" + section)
      .closest(".elementor-container")
      .parent()
      .parent();

    for (var e in jade.expressionFields) {
      var expr = jade.expressionFields[e];
      var value = sectionData["expr-values"][expr];
      p.find('[name="result-' + expr + '"]').text(value);
    }

    // List of Found words
    var analysisContainer = p.find("[name=resultrow]");
    var rowTemplate = analysisContainer.html();
    analysisContainer.html("");

    for (var a in sectionData.analysis) {
      var rowHTML = rowTemplate;
      var row = sectionData.analysis[a];

      rowHTML = rowHTML.replace("{{result-word}}", row.word);
      rowHTML = rowHTML.replace("{{result-expression}}", row.expression);
      rowHTML = rowHTML.replace(
        "{{result-alternatives}}",
        row.alternative.stripSlashes()
      );

      analysisContainer.html(analysisContainer.html() + rowHTML);

      var colorClass = jade.findColorClass(row.expression);

      // If the word does not exist
      if (words.findIndex((e) => e.highlight == row.word) == -1) {
        // Add
        words.push({
          highlight: row.word,
          className: colorClass,
        });

        // If the word exists, with different color
      } else if (
        words.findIndex(
          (e) => e.highlight == row.word && e.className != colorClass
        ) >= 0
      ) {
        words.push({
          highlight: row.word,
          className: colorClass + "-underline",
        });
      }
    }

    // Highlight
    jQuery("#" + section + "-highlight").highlightWithinTextarea({
      highlight: words,
    });
  }
}

jade.preparePrintPDF = () => {
  jQuery(".elementor-button-wrapper").attr("data-html2canvas-ignore", true);
  jQuery(".smart-page-loader").attr("data-html2canvas-ignore", true);
  jQuery(".screen-reader-text").attr("data-html2canvas-ignore", true);
  jQuery("#wpadminbar").attr("data-html2canvas-ignore", true);
};

var renderDivHeader = false;
var renderDivFooter = false;
jade.printPDF = () => {
  jade.printPopup(true);

  // https://github.com/eKoopmans/html2pdf.js/issues/19
  // Or Workaround https://github.com/eKoopmans/html2pdf.js/issues/311

  // Idee: Die einzelnen Sections auf Pages Rendern

  const zoom = "100";

  if (renderDivHeader == false) {
    jQuery("body").append(
      '<div id="renderdivheader" style="zoom: ' + zoom + '%;"></div>'
    );
    jQuery("body").append(
      '<div id="renderdivfooter" style="zoom: ' + zoom + '%;"></div>'
    );
    renderDivHeader = jQuery("#renderdivheader");
    renderDivFooter = jQuery("#renderdivfooter");
  }

  renderDivHeader.empty();
  renderDivFooter.empty();

  let bodyFontSave = jQuery("body").css("font-size");
  jQuery("body").css("font-size", "12px");

  let header = jQuery("[data-elementor-type=header]").clone();
  jade.replaceClassForPDF(header);
  header.appendTo(renderDivHeader);

  let topPart = jQuery("[data-id=46585f1]").clone();
  jade.replaceClassForPDF(topPart);
  topPart.find("[right-of-svg-cell]").remove();
  topPart
    .find("[left-and-svg-cell]")
    .removeClass("elementor-col-50")
    .addClass("elementor-col-100");
  topPart.appendTo(renderDivHeader);

  var filename =
    "JobAdDecoder_" + jade.title.trim().replace(/[^a-z0-9]/gi, "_") + ".pdf";

  var pages = jQuery("[pdfpage]");
  var worker = html2pdf()
    .set({
      margin: [10, 20, 5, 20], // [top, left, bottom, right]
      filename: filename,
      image: { type: "jpeg", quality: 1 },
      pagebreak: { avoid: ".dontbreak" },
      html2canvas: {
        scale: 2,
        dpi: 192,
        letterRendering: true,
        scrollX: 0,
        scrollY: 0,
        onclone: (element) => {
          const svgElements = Array.from(element.querySelectorAll("svg"));
          console.log(svgElements);
          svgElements.forEach((s) => {
            const bBox = s.getBBox();
            //s.setAttribute("x", bBox.x);
            //s.setAttribute("y", bBox.y);
            s.setAttribute("width", s.scrollWidth);
            s.setAttribute("height", s.scrollHeight);
          });
        },
      },
      jsPDF: {
        unit: "mm",
        format: "a4",
        orientation: "portrait",
        compress: false,
      },
    })
    .from(renderDivHeader[0])
    .toPdf();

  var pages = jQuery("[pdfpage]");
  for (let j = 0; j < pages.length - 1; j++) {
    let part = jQuery(pages[j]).clone();
    jade.replaceClassForPDF(part);

    part
      .find("[kill-this-pdf]")
      .prev()
      .removeClass("elementor-col-50")
      .addClass("elementor-col-100");
    part.find("[kill-this-pdf]").remove();

    worker = worker
      .get("pdf")
      .then((pdf) => {
        pdf.addPage();
      })
      .from(part[0])
      .toContainer()
      .toCanvas()
      .toPdf();
    jade.replaceClassForPDF(part, true);
  }

  let pageFooter = jQuery(pages[pages.length - 1]).clone();
  jade.replaceClassForPDF(pageFooter, true); // MP, 28.01.22 - der "true"-Parameter wurde ausgelassen
  pageFooter.appendTo(renderDivFooter);

  let siteFooter = jQuery(".site-footer").clone();
  jade.replaceClassForPDF(siteFooter);
  siteFooter.appendTo(renderDivFooter);

  worker = worker
    .get("pdf")
    .then((pdf) => {
      pdf.addPage();
    })
    .from(renderDivFooter[0])
    .toContainer()
    .toCanvas()
    .toPdf();

  worker.save().then(function () {
    renderDivHeader.empty();
    renderDivFooter.empty();
    jQuery("body").css("font-size", bodyFontSave);

    if (jade.printpdf) {
      setTimeout(function () {
        window.close();
      }, 1000);
    } else {
      jade.printPopup(false);
    }
  });
};

jade.replaceClassForPDF = (element, remove) => {
  if (remove) {
    element
      .find(".hwt-container-pdf")
      .addClass("hwt-container")
      .removeClass("hwt-content-pdf");
    element
      .find(".hwt-backdrop-pdf")
      .addClass("hwt-backdrop")
      .removeClass("hwt-backdrop-pdf");
    element
      .find(".hwt-highlights-pdf")
      .addClass("hwt-highlights")
      .removeClass("hwt-highlights-pdf");
    element
      .find(".hwt-input-pdf")
      .addClass("hwt-input")
      .removeClass("hwt-input-pdf");
    element
      .find(".hwt-content-pdf")
      .addClass("hwt-content")
      .removeClass("hwt-content-pdf");
    //element.find(".divCellSVG-pdf").addClass("divCellSVG").removeClass("divCellSVG-pdf");
  } else {
    element
      .find(".hwt-container")
      .addClass("hwt-container-pdf")
      .removeClass("hwt-container");
    element
      .find(".hwt-backdrop")
      .addClass("hwt-backdrop-pdf")
      .removeClass("hwt-backdrop");
    element
      .find(".hwt-highlights")
      .addClass("hwt-highlights-pdf")
      .removeClass("hwt-highlights");
    element
      .find(".hwt-input")
      .addClass("hwt-input-pdf")
      .removeClass("hwt-input");
    element
      .find(".hwt-content")
      .addClass("hwt-content-pdf")
      .removeClass("hwt-content");
    //element.find(".divCellSVG").addClass("divCellSVG-pdf").removeClass("divCellSVG");
  }
};

jade.findColorClass = (expression) => {
  if (expression.indexOf("älter") != -1) return "mark-elder";
  if (expression.indexOf("jünger") != -1) return "mark-younger";
  if (expression.indexOf("männlich") != -1) return "mark-male";
  if (expression.indexOf("weiblich") != -1) return "mark-female";

  return "mark-notfound";
};

jade.modal = false;
jade.printPopup = (show) => {
  if (!show) {
    if (jade.modal) jade.modal.fadeOut();
    return;
  }

  if (!jade.modal) {
    jQuery("body").append(
      '<div id="print-modal">\n' +
        "\n" +
        "  <!-- Modal content -->\n" +
        '  <div class="print-modal-content">\n' +
        '    <span class="print-modal-close">&times;</span>\n' +
        "    <p>PDF wird generiert ...</p>" +
        "  </div>\n" +
        "</div>"
    );

    jade.modal = jQuery("#print-modal");

    jQuery(".print-modal-close").click((e) => {
      e.preventDefault();
      jade.modal.fadeOut();
    });
  }

  jade.modal.fadeIn();
};
