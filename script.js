document.addEventListener("DOMContentLoaded", function() {
    const questionContainer = document.getElementById("questionContainer");

    for (let i = 1; i <= 25; i++) {
        const questionDiv = document.createElement("div");
        questionDiv.classList.add("question");
        questionDiv.innerHTML = `
            <h6>Q${i} :
            <label><input type="radio" name="q${i}" value="A"> A</label>
            <label><input type="radio" name="q${i}" value="B"> B</label>
            <label><input type="radio" name="q${i}" value="C"> C</label>
            <label><input type="radio" name="q${i}" value="D"> D</label>
            </h6>
        `;
        questionContainer.appendChild(questionDiv);
    }

    showLoader();
    openPDF();
});


function submitForm() {
    const formData = {};

    for (let i = 1; i <= 25; i++) {
        formData[`Question ${i}`] = getSelectedOption(`q${i}`);
    }

    generateCSV(formData);

}

function getSelectedOption(name) {
    const options = document.getElementsByName(name);
    for (let i = 0; i < options.length; i++) {
        if (options[i].checked) {
            return options[i].value;
        }
    }
    return "Not Selected";
}

function generateCSV(data) {
    const csvContent = "Question,Response\n" +
                       Object.keys(data).map(key => `"${key}","${data[key]}"`).join("\n");

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "OMR_Responses.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function showLoader() {
    const loader = document.getElementById("loader");
    loader.style.display = "block";
}

function hideLoader() {
    const loader = document.getElementById("loader");
    loader.style.display = "none";
}

function openPDF() {
    const pdfUrl = "MTP_11.pdf";
    const iframeElement = document.createElement("iframe");
    iframeElement.src = pdfUrl;
    iframeElement.width = "100%";
    iframeElement.height = "900px";
    iframeElement.style.border = "none"; // Optional: Remove border

    const pdfViewer = document.getElementById("pdfViewer");
    pdfViewer.innerHTML = "";
    pdfViewer.appendChild(iframeElement);

    // Hide the loader once the PDF is loaded
    iframeElement.onload = function() {
        hideLoader();
    };
}
