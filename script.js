document.addEventListener("DOMContentLoaded", function() {
    const questionContainer = document.getElementById("questionContainer");
    
    for (let i = 1; i <= 25; i++) {
        const questionDiv = document.createElement("div");
        questionDiv.innerHTML = `
            <h6>Q${i} : 
            A <input type="radio" name="q${i}" value="A">
            B <input type="radio" name="q${i}" value="B">
            C <input type="radio" name="q${i}" value="C">
            D <input type="radio" name="q${i}" value="D">
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
    const embedElement = document.createElement("embed");
    embedElement.src = pdfUrl;
    embedElement.type = "application/pdf";
    embedElement.width = "100%";
    embedElement.height = "800px";
    
    const pdfViewer = document.getElementById("pdfViewer");
    pdfViewer.innerHTML = "";
    pdfViewer.appendChild(embedElement);

    embedElement.onload = function() {
        hideLoader();
    };
}
