function toggleLoader($identifier) {
    document.getElementById("loader" + $identifier).classList.toggle("loader");
    document.getElementById("btn" + $identifier).disabled = true; 
    document.getElementById("height" + $identifier).disabled = true; 
    document.getElementById("width" + $identifier).disabled = true;
    document.getElementById("chkUid" + $identifier).disabled = true;
}

function toggleImage($identifier) {
    document.getElementById("img" + $identifier).classList.toggle("show");
}

function toggleLoaderAll() {
    document.getElementById("loaderAll").classList.toggle("loader");
}


function changeColorOfRow() {
    $("tr.sub").each(function(){
       var classes = $(this).attr("class");
       var classArray = classes.split(" ");
       for(var i = 0; i<=classArray.length-1; i++) {
           if(classArray[i].startsWith("color")) {
               var number = classArray[i].replac("color", "");
               $(this).attr("style", "background-color: rgb(255, 255," + ((100-number)/100)*255 + ");");
           }
       }
    });
}

function openInfo(identifier) {
    try {
        if (typeof top.TYPO3.InfoWindow.showItem === 'function') {
            top.TYPO3.InfoWindow.showItem( '_FILE', '1:' + identifier);
            return false;
        } else {
            top.launchView('_FILE', '1:' + identifier);
            return false;
        }
    } catch (e) {
        top.launchView('_FILE', '1:' + identifier);
        return false;
    }
}

