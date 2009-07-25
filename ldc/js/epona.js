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
    init_form();
}


var DAVID;

/******************************************************************************
 * OPERATIONS variable
 *****************************************************************************/

/*
 * Find a Operation from id
 * @param id: id of the operation
 * @return operation object
 */
function remove_operation_from_id(id)
{
    var i = 0;
    var id_c;
    do {
        id_c = OPERATIONS[i].id;
        i++;
    } while(id_c != id);
    delete(OPERATIONS[i-1]);
    OPERATIONS.sort(sortByDate);
    OPERATIONS.length--;
}


/******************************************************************************
 * Initialisation de tout
 *****************************************************************************/
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
            url: "ldc/api_xml/api_operations.php",
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
            url: "ldc/api_xml/api_cat.php?op=get",
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

/******************************************************************************
 * Liste des Opérations
 * ***************************************************************************/
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
    $Tr.append("<th></th>");
    $Tr.append("<th></th>");
    $Thead.append($Tr);
    $Table.append($Thead);
    /* body */
    for (var i in OPERATIONS) {
        var op = OPERATIONS[i];
        $Table.append(operation_html(op));
    }
    $div.append($Table);


    /* 
     * Events on oprations table
     */

    /* click sur les labels */
    $("a.label").live("click",
        function() {
            var label_id = $(this).attr("href");
            filter_on_labels(label_id);
            return false;
        }
    );

    /* click sur les categories */
    $("a.cat").live("click",
        function () {
            var id = $(this).attr("href");
            display_another_categorie(id)
            return false;
        }
    );

    /* click on delete */
    $("#jie .ui-icon-closethick").live("click",
        function() {
            var id = $(this).parents('tr').children("td.op-id").text();
            remove_operation(id, $(this).parents('tr'));
            return false;
        }
    );

    /* click on edit */
    $("#jie .ui-icon-pencil").live("click",
        function() {
            alert("Not yet implemented");
            return false;
        }
    );




    /* remove a operation */
    function remove_operation(id, $tr)
    {
        /* AJAX: remove operation in server */
        $.get("ldc/api_xml/del_operation.php", {"id":id}, parse_result, "json");
        /* 1. update operations list */
        $tr.remove();
        /* update OPERATIONS variable */
        remove_operation_from_id(id);
        /* update STATS variable */
        init_stats();
        /* update graph */
        display_another_categorie(0);

        function parse_result(json)
        {
            // TODO faire quelque chose de plus constructif
            alert(json.result);
        }
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
            labels_str += '<a class="label" href="'+labels[j].id+'">'+labels[j].name+'</a> ';
        }
        var ul = '<ul>';
        for(var j in op.categorie) {
            var cat = op.categorie[j];
            //var name = CATEGORIES[cat.id].name;
            var name = categorie2str(CATEGORIES[cat.id]);
            var li = '<li><a class="cat" href=\"'+cat.id+'\">'+name+' : '+cat.value+'</a></li>';
            ul += li;
        }
        ul += '</ul>';

        var $tr = $("<tr>");
        $tr.append($('<td class="op-id">'+id+'</td>'));
        $tr.append($('<td class="op-date">'+date2str(date)+'</td>'));
        $tr.append($('<td class="op-credit">'+credit+'</td>'));
        $tr.append($('<td class="op-debit">'+debit+'</td>'));
        $tr.append($('<td class="op-cat">').append(ul));
        $tr.append($('<td class="op-labels">'+labels_str+'</td>'));
        var $icon = add_icon("ui-icon-pencil");
        $tr.append($('<td></td>').append($icon));
        $icon = add_icon("ui-icon-closethick");
        $tr.append($('<td></td>').append($icon));
        return $tr;

    }


}

    function add_icon(name) {
        var $div = $("<div class=\"ui-widget ui-state-default\"></div>");
        $div.append("<div class=\"ui-icon "+name+"\"></div>");
        $div.hover(
                 function() { $(this).addClass('ui-state-hover'); }, 
                 function() { $(this).removeClass('ui-state-hover'); }
                );
        return $div;
    }




/* ****************************************************************************
 *                     FORMULAIRE
 * ***************************************************************************/

/* Init the form. Called once! */
function init_form() {
    
    /* formulaire */
    $("#form-add").dialog({ buttons: { "Annuler": function() { $("#form-add").dialog("close"); },"Envoyer": send_form },
                                modal: true,
                                overlay: { opacity: 0.5, background: "black" },
                                title: "Ajout d'opérations",
                                autoOpen: false,
                                zIndex:900,
                                width: 450,
                                height: 300});

    $("#op-calendar").datepicker({dateFormat: 'dd-mm-yy', buttonImage: 'images/calendar.gif', buttonImageOnly: true, showOn: 'button'});

    /* creer les catégories */
    var list = '';
    create_cat_list(0);
    $("#tree").append(list);

    /* ajouter une ligne */
    var nb_row = 1;
    add_row($("#form-add tbody"));


    /* EVENTS */

    /* afficher les categories quand on clique au bon endroit */
    $(".need-tree").live("click", function () {
        $(this).addClass("selected");
        var pos = $(this).position();
        var posX = pos.x;
        var posY = pos.y + 25;
        var $Tree = $("#tree");
        $Tree.css("top", posY);
        $Tree.css("left", posX);
        $Tree.fadeIn("slow");
        return false;
    });

    /* init le form quand on choisie une catégorie */
    $("#tree a").live("click", function() {
            var id = $(this).attr("href");
            var name = $(this).text();
            var selected = $(".selected");
            selected.val(name);
            selected.prev(".op-cat-id").val(id);
            $(".selected").removeClass("selected");
            $("#tree").fadeOut('fast');
            return false;
    });

    /* ajout d'une ligne */
    $(".ui-icon-plusthick").click(function() {
            add_row($("#form-add tbody"));
    });

    /* ajout d'une opération */
    $("#add-op").click(
        function() {
            $("#form-add").dialog("open");
        }
    );



    /* FONCTIONS */

    /* ajouter une ligne */
    function add_row($j)
    {
        var $row = $('<tr></tr>');
        var row = '<td>';
        row += '<input type="hidden" id="op-cat-id_'+nb_row+'" name="op-cat-id_'+nb_row+'" class="op-cat-id"/>';
        row += '<input type="text" id="" name="" class="need-tree op-cat-name" />';
        row += '</td>';
        row += '<td>';
        row += '<input type="text" id="op-cat-value_'+nb_row+'" name="op-cat-value_'+nb_row+'" class="op-cat-value"/>';
        row += '</td>';
        var $td = $('<td></td>');
        var $icon = add_icon("ui-icon-closethick");
        $row.append(row);
        $td.append($icon);
        $row.append($td);
        $j.append($row);
        nb_row++;

        /* suppression d'une ligne */
        $icon.click(function() {
                debug("sup");
                var nb_row_selected = $(this).closest('tr').remove();
                });
    }

    function create_cat_list(id)
    {
        var name = CATEGORIES[id].name;
        var father = CATEGORIES[id].fatherId;
        list +='<li><a href="'+id+'">'+name+'</a></li>';
        if (!CATEGORIES_BY_FATHER[id])
            return list;
        list += '<ul>';
        for( i in CATEGORIES_BY_FATHER[id]) {
            var child_id = CATEGORIES_BY_FATHER[id][i];
            create_cat_list(child_id);
        }
        list += '</ul>';
        return list;
    }


    function send_form(){
        $(".error").removeClass("error");
        /* TODO vérification du formulaire */

        /* retrieve information from form */

        // type
        var type = $("#op-type").val();

        // date
        var date = $("#op-calendar").val();
        if (date == '') {
            $("li.calendar").addClass("error");
            warning("date invalide");
        }

        //cats
        var cats = [];
        $("#form table tbody tr").each(
            function() {
                // retrieve cat info
                var cat_id = $(this).children().children(".op-cat-id").val();
                var cat_name = $(this).children().children(".op-cat-name").val();
                var cat_value = parseFloat($(this).children().children(".op-cat-value").val());
                // test on values
                if (cat_id == '' || cat_name == '') {
                    $(this).children().children(".op-cat-name").addClass("error");
                }
                if (isNaN(cat_value)) {
                    $(this).children().children(".op-cat-value").addClass("error");
                }
                // if OK, construct object
                if ($(this).children().children(".error").length == 0) {
                    var cat = { 'id' : cat_id, 'value' : cat_value };
                    debug("Cat.: "+cat);
                    // add object in list
                    cats.push(cat);
                }
            }
        );

        // labels
        var labels = $("#op-tags").val();
        var tmp = labels.split(/, /);
        labels = [];
        nb = 0;
        for(i in tmp) {
            labels[nb] = {'name': tmp[i]};
        }


        /* debug */
        debug("type="+type);
        debug("date="+date);
        for(i in cats) {
            debug("cat id:"+cats[i].id+" value:"+cats[i].value);
        }
        for(i in labels) {
            debug("labels: "+labels[i].name);
        }

        /* stop if error in form */
        if ($(".error").length >= 1) {
            return false;
        }


        var $form = $("#form");
        var s = $form.serialize(); 
        $.ajax({ 
                type: "POST", 
                data: s, 
                url: $form.attr("action"), 
                success: send_form_success,
                error: send_form_error}
            );
        /* ajout de l'opération dans le tableau sans attendre le résultat de la requete */

        /* conversion de la date calendar en date MySQL */
        var date = $("#op-calendar").val();
        var tmp = date.split(/-/);
        date = tmp[2]+'-'+tmp[1]+'-'+tmp[0];

        var operation = {
            date : date,
            id : "33",
            value : "1000",
            confirm : "1",
            categorie : [{ id:"0", value:"1000"}],
            labels :  [{ id:"1", name:"tag"}]
        };
        OPERATIONS.push(operation);
        add_operation_in_stats(operation);
        display_all_operations("#jie");
        display_another_categorie(0);


        $("#form-add").dialog("close");

        function send_form_success(ret)
        {
            alert("OK");
            return false;
        }

        function send_form_error(ret)
        {
            alert("Aïe!!!");
            return false;
        }


    }
}

/***************** FIN DU FORMULAIRE *****************************************/



/*
 * Filtre sur les opérations
 */
function show_operation_from_cat(cat_id) {
    var selector = generate_selector(cat_id);
    selector=selector.substring(0,selector.length-1);
    /* hide everything that contains another cat */
    $("tr:has(a.cat[href!="+cat_id+"])").hide();
    /* show right line */
    $(selector).show();

    function generate_selector(cat_id) {
        var selector = "";
        if (CATEGORIES_BY_FATHER[cat_id]) {
            for(var i in CATEGORIES_BY_FATHER[cat_id]) {
                selector += generate_selector(CATEGORIES_BY_FATHER[cat_id][i]);
            }
        }
        selector += "tr:has(a.cat[href="+cat_id+"]),";
        return selector;
    }
}


/*
 * Display all operations, stats... about a tag
 */
function filter_on_labels(label_id)
{
    /* filter on operations table */
    $("tr:has(a.label[href!="+label_id+"])").hide();
    $("tr:has(a.label[href="+label_id+"])").show();
    /* filter on statistics */
    // TODO
}


/*
 * Pour afficher une catégorie en particulier
 */
function display_another_categorie(id) {
    var data = [];
    data[0] = generate_data_for_cat(id);
    var name = categorie2str(CATEGORIES[id]);
    /* update title */
    magic_cat_menu(id);
    /* update graph */
    var labels = generate_labels();
    $("#ofc").ofc('update', {"values": data, "title":name, "labels": labels});
    /* update table */
    show_operation_from_cat(id);
}


/*
 * Pour générer les données pour ofc
 */
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

/*
 * Générer les labels pour ofc
 */
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


/* pour augementer une date d'un mois */
function increase_date_by_month(date) {
    date.month++;
    if (date.month == 13) {
        date.month = 1;
        date.year++;
    }
    return date;
}

/* Générer le menu des categories */
function magic_cat_menu(id) {
    var $mc = $("#magic-cat");
    $mc.empty();
    /* title */
    var title = "<a href=\""+id+"\">"+CATEGORIES[id].name+"</a>";
    var father_id = CATEGORIES[id].fatherId;
    while(father_id != -1) {
        var id_c = father_id;
        var name = CATEGORIES[id_c].name;
        if (id_c != 0) {
            father_id =  CATEGORIES[id_c].fatherId;
        } else {
            father_id = -1
        }
        title = "<a href=\""+id_c+"\">"+name+"</a> >> "+title;
    }
    title = '<h1>'+title+'</h1>';
    $mc.append(title);
    var ul = "<ul>";
    for(var i in CATEGORIES_BY_FATHER[id]) {
        var id2 = CATEGORIES_BY_FATHER[id][i];
        ul += "<li><a href=\""+id2+"\">"+CATEGORIES[id2].name+"</a></li>";
    }
    ul += "</ul>";
    $mc.append(ul);
}

$("#magic-cat a").live("click", function() {
        var id = $(this).attr("href");
        display_another_categorie(id);
        return false;
});



/* 1er affichage
 * TODO améliorer ca...
 * */
function display_stats() {
    var data = [];
    data[0] = generate_data_for_cat(0);
    var labels = generate_labels();
    $("#ofc").ofc('add', {"values": data, "height":"250", "width":"100%", 'labels':labels, 'title':"Total"});
    /* Affichage du menu des catégories */
    magic_cat_menu(0);
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
    STATS = [];
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

jQuery.fn.extend({ 
   position : function() { 
       obj = $(this).get(0); 
       var curleft = obj.offsetLeft || 0; 
       var curtop = obj.offsetTop || 0; 
       while (obj = obj.offsetParent) { 
                curleft += obj.offsetLeft 
                curtop += obj.offsetTop 
       } 
       return {x:curleft,y:curtop}; 
   } 
}); 

