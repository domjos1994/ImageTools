
function toggleLoader($identifier) {
    document.getElementById("loader" + $identifier).classList.toggle("loader");
    document.getElementById("btn" + $identifier).disabled = true; 
    document.getElementById("height" + $identifier).disabled = true; 
    document.getElementById("width" + $identifier).disabled = true;
    document.getElementById("chkUid" + $identifier).disabled = true;
}

function toggleLoaderAll() {
    document.getElementById("loaderAll").classList.toggle("loader");
}


