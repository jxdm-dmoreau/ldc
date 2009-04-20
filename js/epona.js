/* Tableau conteant toutes les operations */
var OPERATIONS = new Array();
var CATEGORIES_BY_FATHER = new Array();
var CATEGORIES = new Array();
var STATS = [];
var STATS2 = [];

var MONTH = [];
MONTH[1] = "Janvier";
MONTH[2] = "Février";
MONTH[3] = "Mars";
MONTH[4] = "Avril";
MONTH[5] = "Mai";
MONTH[6] = "Juin";
MONTH[7] = "Juillet";
MONTH[8] = "Aout";
MONTH[9] = "Septembre";
MONTH[10] = "Octobre";
MONTH[11] = "Novembre";
MONTH[12] = "Décembre";

/* FONCTION D'INIT */
function eponaRegister()
{
    $("#tabs").tabs();
    epona_init();
    display_all_operations('#jie');
    display_stats();
}




/* VERSION FINALE QUE JE PRESENTERAI A LA FEMME DE MA VIE: JIE XING */

/*  */
function epona_init() {

    init_operations();
    init_categories();
    init_stats();

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
            CATEGORIES['0'] = { 'name':'Total', 'id' : 0, 'fatherId' : -1};
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
        //var name = CATEGORIES[cat.id].name;
        var name = categorie2str(CATEGORIES[cat.id]);
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

function generate_data_for_cat(id) {
    // extract the first and last date
    OPERATIONS.sort(sortByDate);
    var dateEnd = extract_date(OPERATIONS[0].date);
    var date = extract_date(OPERATIONS[OPERATIONS.length-1].date);

    // loop on each month
    var data = [];
    while(!(date.year > dateEnd.year || (date.year == dateEnd.year &&  date.month > dateEnd.month))) {
        // code here...
        if (!STATS[id] || !STATS[id][date.year] || !STATS[id][date.year][date.month]) {
            data.push(0);
        } else {
            data.push(STATS[id][date.year][date.month]);
        }

        date = increase_date_by_month(date);
    }
    return data;
}

function generate_labels() {
    // extract the first and last date
    OPERATIONS.sort(sortByDate);
    var dateEnd = extract_date(OPERATIONS[0].date);
    var date = extract_date(OPERATIONS[OPERATIONS.length-1].date);

    // loop on each month
    var labels = [];
    while(!(date.year > dateEnd.year || (date.year == dateEnd.year &&  date.month > dateEnd.month))) {
        // code here...
        var month_str = MONTH[date.month];
        labels.push(month_str+" "+date.year);
        date = increase_date_by_month(date);
    }
    return labels;
}

function increase_date_by_month(date) {
    date.month++;
    if (date.month == 13) {
        date.month = 1;
        date.year++;
    }
    return date;
}


function display_stats() {
    var data = [];
    data[0] = generate_data_for_cat(0);
    var labels = generate_labels();
    $("#ofc").ofc('add', {"values": data, "height":"250", "width":"800", 'labels':labels});
    // fill the select (combo-box)
    fill_select(0);
    $("#select_cat").change(function() {
            var id = $("#select_cat").val();
            data[0] = generate_data_for_cat(id);
            $("#ofc").ofc('update', {"values": data});
        });



    function fill_select(id) {
        var name = categorie2str(CATEGORIES[id]);
        $("#select_cat").append("<option value=\""+id+"\">"+name+"</option>");
        for(var i in CATEGORIES_BY_FATHER[id]) {
            fill_select(CATEGORIES_BY_FATHER[id][i]);
        }
    }

    /* magic cat */
    var $ul = $("<ul>");
    construct_cat_list(0, $ul);
    $("#magic-cat").append($ul);

    function construct_cat_list(id, $balise) {
        var name = CATEGORIES[id].name;
        var $li = $("<li cat=\""+id+"\">");
        $li.click(function() {
            var id = $(this).attr("cat");
            $(this).addClass("selected");
            data[0] = generate_data_for_cat(id);
            $("#ofc").ofc('update', {"values": data});
        });
        $li.append(name);
        $balise.append($li);
        if (CATEGORIES_BY_FATHER[id]){
            var $ul = $("<ul>");
            for(var i in CATEGORIES_BY_FATHER[id]) {
                construct_cat_list(CATEGORIES_BY_FATHER[id][i], $ul);
            }
            $balise.append($ul);
        }
    }




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

function categorie2str(cat) {
    var my_cat = cat.name;
    var father_id = cat.fatherId;
    while(father_id != 0 && father_id != -1) {
        if (!CATEGORIES[father_id]) alert(father_id);
        my_cat = CATEGORIES[father_id].name + ">>" + my_cat;
        if (father_id != 0) father_id = CATEGORIES[father_id].fatherId;
    }
    return my_cat;
}

function sortByDate(opA, opB) {
    var dateA = opA.date;
    var tmpA = dateA.split(/-/);
    var yearA = parseFloat(tmpA[0]);
    var monthA = parseFloat(tmpA[1]);
    var dayA = parseFloat(tmpA[2]);
    var dateB = opB.date;
    var tmpB = dateB.split(/-/);
    var yearB = parseFloat(tmpB[0]);
    var monthB = parseFloat(tmpB[1]);
    var dayB = parseFloat(tmpB[2]);
    if (yearA > yearB)   return -1;
    if (yearA < yearB)   return 1;
    if (monthA > monthB) return -1;
    if (monthA < monthB) return 1;
    if (dayA > dayB)     return -1;
    if (dayA < dayB)     return 1;
    // same date, return the...?
    return 1;
}


function init_stats() {
    for(var i in OPERATIONS) {
        add_operation_in_stats(OPERATIONS[i]);
    }
}

function extract_date(date) {
    var tmp = date.split(/-/);
    var year = parseFloat(tmp[0]);
    var month = parseFloat(tmp[1]);
    var day = parseFloat(tmp[2]);
    return {'day':day, 'month':month, 'year':year};
}

function add_operation_in_stats(op) {

        var date = extract_date(op.date);

        /* loop on categories */
        for(var i in op.categorie) {
            var id = op.categorie[i].id;
            /* loop on fathers */
            add_value_in_stats(date.year, date.month, id, op.categorie[i].value);
        }

    function add_value_in_stats(year, month, id, value) {
        if(!CATEGORIES[id]) {
            alert("CATEGORIES["+id+"] not defined!");
            return undefined;
        }
        if (!STATS[id])              STATS[id] = [];
        if (!STATS[id][year])        STATS[id][year] = [];
        if (!STATS[id][year][month]) STATS[id][year][month] = 0;
        STATS[id][year][month] += parseFloat(value);

        // father
        var fatherId = CATEGORIES[id].fatherId;
        if (fatherId != -1) {
            add_value_in_stats(year, month, fatherId, value);
        }
    }
}

function debug(msg) {
    if (window.console && window.console.log)
        window.console.log(msg);
};

function error(msg) {
    if (window.console && window.console.error)
        window.console.error(msg);
};
function warning(msg) {
    if (window.console && window.console.warn)
        window.console.warn(msg);
};
