/* Tableau conteant toutes les operations */
var OPERATIONS = new Array();
var CATEGORIES_BY_FATHER = new Array();
var CATEGORIES = new Array();


/* OPERATIONS */
var EponaOperations = new Array();

/* CATEGORIES*/
var EponaCategories = new Array();
var EponaCategoriesById = new Array();
var EponaCategoriesChildren = new Array();

/* STATS */
var EponaStatsByCat = new Array();
var EponaStats = new Array();


/* FONCTION D'INIT */
function eponaRegister()
{
    $("#tabs").tabs();
    var values = new Array(0,2,4,12,2,3,6);
    var values_tab = [];
    values_tab[0] = [0,2,5,4,1,2,3];
    values_tab[1] = [1,1,1,4,5,34,4];
    values_tab[2] = [10,12,1,5,7,9,10];
    values_tab[3] = [1.2,8,3,3,1,2,4];
    $("#ofc").ofc('add', {"values": values_tab, "height":"250", "width":"800"});
    epona_init();
    display_all_operations('#jie');
}

function debug(msg) {
    $("#log").append("<p>"+msg+"</p>");
}

/******************************************************************************
 * FONCTION OPERATIONS
 *****************************************************************************/
function loadOperationsList()
{

    function addOpInStats(year, month, id, value) {
        if(!EponaCategoriesById[id]) {
            alert("EponaCategoriesById["+id+"] not defined!");
        }
        var fatherId = EponaCategoriesById[id].fatherId;
        if (!EponaStats[id]) {
             EponaStats[id] = new Array();
        }
        if (!EponaStats[id][year]) {
            EponaStats[id][year] = new Array();
        }
        if (!EponaStats[id][year][month]) {
            EponaStats[id][year][month] = 0;
        }
        debug("Ajout de "+value+" pour la catégorie"+id);
        EponaStats[id][year][month] += parseFloat(value);
        if (fatherId != 0) {
            addOpInStats(year, month, fatherId, value);
        }
    }

    function generateStats(op)
    {
        for(var i=0; i < op.categorie.length; i++) {
            var id = op.categorie[i].id;
            if (!EponaStatsByCat[id]) {
                 EponaStatsByCat[id] = 0;
            }
            EponaStatsByCat[id] += parseFloat(op.categorie[i].value);
        }

        /* STATS V2 */

        /* split date */
        var tmp = op.date.split(/-/);
        var year = tmp[0];
        var month = tmp[1];

        /* loop on categories */
        for(var i = 0; i < op.categorie.length; i++) {
            var id = op.categorie[i].id;
            /* loop on fathers */
            addOpInStats(year, month, id, op.categorie[i].value);
        }
        return false;
    }



        /*******************************************
         * Ajout des Stats dans la page
         * ****************************************/
        /*
        var jTableStats = $('<table></table>');
        for(var i in EponaStatsByCat) {
            var id =  i;
            var name = EponaCategoriesById[id].name;
            var value = EponaStatsByCat[i];
            jTableStats.append($('<tr><td>'+name+'</td><td>'+value+'</td></tr>'));
        }
        $("#tabs-1").append(jTableStats);

        for(var catId in EponaStats) {
            for(var year in EponaStats[catId]) {
                for(var month in EponaStats[catId][year]) {
                    if (!EponaCategoriesById[catId])
                        alert(catId);
                    var catName = EponaCategoriesById[catId].name;
                    debug("<p>"+year+"-"+month+" ["+catName+"]: "+EponaStats[catId][year][month]+"</p>");
                }

            }
        }
        */



}






/* VERSION FINALE QUE JE PRESENTERAI A LA FEMME DE MA VIE: JIE XING */

/*  */
function epona_init() {

    init_operations();
    init_categories();

    /* 
     * initiliser la liste des opérations
     */
    function init_operations() {
        /* get operations list */
        $.ajax({
            type: "GET",
            url: "api_xml/api_operations.php",
            success: parse_xml,
            async: false,
            error : function (ret) { $("#log").append(ret); return false; }

        });

        function parse_xml(xml) {

            $xml = $(xml);
            $xml.find("operation").each(function() {
                var op = {
                    "id"     : $(this).attr("id"),
                    "date"   : $(this).find('date').text(),
                    "value"  : $(this).find('value').text(),
                    "confirm": $(this).find('confirm').text()
                };
                /* categories */
                op.categorie = new Array();
                $(this).find('categorie').each( function() {
                    op.categorie.push({
                        "id": $(this).attr("id"),
                        "value": $(this).text()
                        });
                    });
                /* labels */
                op.labels = new Array();
                $(this).find('label').each( function() {
                    op.labels.push({
                        "id": $(this).attr("id"),
                        "name": $(this).text()
                        });
                    });
                /* ajout dans la liste */
                OPERATIONS.push(op);
            });
        }
    }


    /*
     * Initialiser la liste des catégories
     */
    function init_categories() {
        $.ajax({
            type: "GET",
            async: false,
            url: "api_xml/api_cat.php?op=get",
            success: parse_xml,
            error : function (ret) { $("#log").append(ret); return false; }
        });

        function parse_xml(xml)
        {
            $Xml = $(xml);
            $Xml.find("categorie").each( function() {
                var name = $(this).attr('name');
                var fatherId = $(this).attr('father_id');
                var id = $(this).attr('id');
                var cat = {
                    'id': id,
                    'name': name,
                    'fatherId': fatherId
                };
                CATEGORIES[id] = cat;
                if (!CATEGORIES_BY_FATHER[fatherId]) {
                    CATEGORIES_BY_FATHER[fatherId] = [];
                }
                CATEGORIES_BY_FATHER[fatherId].push(id);
            });
            return false;
        }
    }


}


/* Affichage */

function display_all_operations(id) {
    $div = $(id);
    // on vide
    $div.empty();
    // sort the array by date
    OPERATIONS.sort(sortByDate);
    
    var $Table = $('<table>');
    /* header */
    var $Thead = $('<thead>');
    var $Tr = $('<tr>');
    $Tr.append("<th>id</th>");
    $Tr.append("<th>Date</th>");
    $Tr.append("<th>Crédit</th>");
    $Tr.append("<th>Dédit</th>");
    $Tr.append("<th>Détails</th>");
    $Tr.append("<th>Labels</th>");
    $Thead.append($Tr);
    $Table.append($Thead);
    /* body */
    for (var i in OPERATIONS) {
        var op = OPERATIONS[i];
        $Table.append(operation_html(op));
    }
    $div.append($Table);

}

function operation_html(op) {
    var id = op.id;
    var value = op.value;
    var date = op.date;
    var credit = (value > 0)?value:0;
    var debit = (value > 0)?0:value*(-1);
    var labels = op.labels;
    var labels_str = '';
    for(var j=0; j < labels.length; j++) {
        labels_str += labels[j].name;
    }
    var $ul = $("<ul>");
    for(var j in op.categorie) {
        var cat = op.categorie[j];
        var name = CATEGORIES[cat.id].name;
        var $li = $('<li>');
        $li.append(name+" : "+cat.value);
        $ul.append($li);
    }

    var $tr = $("<tr>");
    $tr.append($('<td>'+id+'</td>'));
    $tr.append($('<td>'+date2str(date)+'</td>'));
    $tr.append($('<td>'+credit+'</td>'));
    $tr.append($('<td>'+debit+'</td>'));
    $tr.append($('<td>').append($ul));
    $tr.append($('<td>'+labels_str+'</td>'));
    return $tr;
}


/*
 * Formate un date (object Mysql) en une date lisible
 * @param date : date sous la forme 1999-12-28
 * @return la date formatée
 */
function date2str(date) {
    var tmp = date.split(/-/);
    var year = tmp[0];
    var month = tmp[1];
    var day = tmp[2];
    var my_date = day+"/"+month+"/"+year;
    return my_date;
}

function sortByDate(opA, opB) {
    var dateA = opA.date;
    var tmpA = dateA.split(/-/);
    var yearA = tmpA[0];
    var monthA = tmpA[1];
    var dayA = tmpA[2];
    var dateB = opB.date;
    var tmpB = dateB.split(/-/);
    var yearB = tmpB[0];
    var monthB = tmpB[1];
    var dayB = tmpB[2];

    if (yearA > yearB)   return -1;
    if (monthA > monthB) return -1;
    if (dayA > dayB)     return -1;
    return 1;
}

