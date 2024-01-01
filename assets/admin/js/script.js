
// Create urlParams query string
var urlParams = new URLSearchParams(window.location.search);

// Get value of single parameter
var sectionName = urlParams.get('page-no');

if(sectionName){

    sectionName = parseInt(sectionName) + 1;
    // sectionName = parseInt(sectionName);
    console.log(sectionName);
    var url = 'users.php?page=mbdcm-sync&page-no=' + sectionName;
    setTimeout(() => {
        window.location.replace(url);
    }, 2000);

}

