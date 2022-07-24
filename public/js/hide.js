function showDiv(divid) {
    // hide or reveal a division
    var div = document.getElementById(divid);
    if (div.style.display == 'block'){
        div.style.display = 'none';
    }
    else {
        div.style.display = 'block';
    }
    return true;
}
