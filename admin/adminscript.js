const currentDirectory = '/var/www/WebTech';

document.addEventListener("DOMContentLoaded", function () {
    const initialPath = "/var/www/WebTech";
    fetchDirectory(initialPath);
});
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("upload-button").addEventListener("click", uploadFiles);
});
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("save-button").addEventListener("click", function (){
        const filePath = document.getElementById("file-preview").getAttribute("data-path");
        if (!filePath) {
            alert("File path is missing!");
            return;
        }
        saveFile(filePath);
    });
});
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("edit-button").addEventListener("click", enableEditing);
});
document.addEventListener("DOMContentLoaded", function () {
    const fileList = document.querySelectorAll(".file-item");
    const filePreview = document.getElementById("file-preview");
    const fileContent = document.getElementById("file-content");
    fileList.forEach(file => {
        file.addEventListener("click", function () {
            const filePath = file.getAttribute("data-path");

            if (!filePath) {
                alert("File path not found for the selected file.");
                return;
            }
            filePreview.setAttribute("data-path", filePath);
            fetchFileContent(filePath);
        });
    });
});

function fetchFileContent(filePath) {
    const url = `/admin/file/preview?path=${encodeURIComponent(filePath)}`;

    fetch(url)
        .then(response => response.text())
        .then(content => {
            const fileContent = document.getElementById("file-content");
            fileContent.value = content;
            fileContent.disabled = true;
        })
        .catch(error => {
            console.error("Error fetching file content:", error);
            alert("Failed to load file content. Please try again.");
        });
}

function enableEditing() {
    const fileContent = document.getElementById("file-content");
    const editButton = document.getElementById("edit-button");
    const saveButton = document.getElementById("save-button");
    const deleteButton = document.getElementById("delete-button");
    fileContent.disabled = false;
    fileContent.focus();
    editButton.style.display = "none";
    saveButton.style.display = "inline-block";
    deleteButton.style.display = "inline-block";
}

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("delete-button").addEventListener("click", function (){
        const filePath = document.getElementById("file-preview").getAttribute("data-path");
        if (!filePath) {
            alert("File path is missing!");
            return;
        }
        deleteFile(filePath);
    });
});

function deleteFile(filePath) {
    if (!filePath) {
        console.error("Invalid input: File path is required.");
        alert("Error: File path is required.");
        return;
    }

    const encodedPath = encodeURIComponent(filePath);

    const url = `/admin/file/delete?path=${encodedPath}`;

    fetch(url, {
        method: "GET",
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "success") {
                alert(data.message);
                console.log("File deleted successfully:", data.message);
                fetchDirectory(currentDirectory);
                clearPreview();

            } else {
                alert(`Error: ${data.message}`);
                console.error("Error:", data.message);
            }
        })
        .catch((error) => {
            console.error("Network error:", error);
            alert("Failed to delete file. Please check your connection and try again.");
        });
}

function uploadFiles() {
    const uploadInput = document.getElementById("upload");
    const file = uploadInput.files[0];

    if (!file) {
        alert("No file selected for upload!");
        return;
    }

    const formData = new FormData();
    formData.append("file", file);

    fetch("/admin/file/upload", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert(data.message);
                fetchDirectory("/var/www/WebTech/uploads");
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error("Error uploading file:", error);
            alert("Failed to upload file. Please try again.");
        });
}


function fetchDirectory(path) {
    const url = `/admin/file/list?path=${encodeURIComponent(path)}`;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const directoryView = document.getElementById("directory-view");
            if (!directoryView) {
                console.error("Element with ID 'directory-view' not found.");
                return;
            }

            directoryView.innerHTML = html;

            addDirectoryClickHandlers();
            addFileClickHandlers();
        })
        .catch(error => {
            console.error("Error fetching directory:", error);
            alert("Failed to load directory. Please try again.");
        });
}



function addDirectoryClickHandlers() {
    const directoryLinks = document.querySelectorAll(".directory a");
    directoryLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const path = this.getAttribute("href").split("path=")[1];
            const decodedPath = decodeURIComponent(path);
            fetchDirectory(decodedPath);
        });
    });
}


function addFileClickHandlers() {
    const fileLinks = document.querySelectorAll(".file a");
    fileLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const path = this.getAttribute("href").split("path=")[1];
            const decodedPath = decodeURIComponent(path);
            fetchFilePreview(decodedPath);
        });
    });
}


function fetchFilePreview(path) {
    const url = `/admin/file/preview?path=${encodeURIComponent(path)}`;

    fetch(url)
        .then(response => response.text()) // Expecting HTML
        .then(html => {
            const filePreviewContainer = document.getElementById("file-preview");

            if (!filePreviewContainer) {
                console.error("File preview container not found.");
                return;
            }
            filePreviewContainer.setAttribute("data-path", path);
            document.getElementById("file-content").value = html;
            document.getElementById("edit-button").style.display = "inline-block";
            document.getElementById("delete-button").style.display = "inline-block";
            document.getElementById("save-button").style.display = "none";
        })
        .catch(error => {
            console.error("Error fetching file preview:", error);
            alert("Failed to load file preview. Please try again.");
        });
}

function clearPreview() {
    const filePreviewContainer = document.getElementById("file-preview");
    const fileContent = document.getElementById("file-content");
    const editButton = document.getElementById("edit-button");
    const saveButton = document.getElementById("save-button");
    const deleteButton = document.getElementById("delete-button");
    filePreviewContainer.setAttribute("data-path", "");
    fileContent.value = "";
    fileContent.disabled = true;
    editButton.style.display = "inline-block";
    saveButton.style.display = "none";
    deleteButton.style.display = "none";
}

function saveFile(filePath){

}
