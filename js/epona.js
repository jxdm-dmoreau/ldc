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
    loadCategories();
    loadOperationsList();
    var values = new Array(0,2,4,12,2,3,6);
    var values_tab = [];
    values_tab[0] = [0,2,5,4,1,2,3];
    values_tab[1] = [1,1,1,4,5,34,4];
    values_tab[2] = [10,12,1,5,7,9,10];
    values_tab[3] = [1.2,8,3,3,1,2,4];
    $("#ofc").ofc('add', {"values": values_tab, "height":"250", "width":"800"});
    values = new Array(23,2,4,5,2,3,6);
    $("#but").click( function() {
        values_tab[0] = [1,2,5,4];
        values_tab[1] = [1,4,1,4];
        values_tab[2] = [10,2,1,5];
        values_tab[3] = [5.2,8,3,3];
        $("#ofc").ofc('update', {"values": values_tab});
    });
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

    function processXml(xmlDoc)
    {
        jXml = $(xmlDoc);

        /****************************************
        * Traitement du fichier XML             *
        ****************************************/
        jXml.find("operation").each(function() {
            var op = {
                "id": $(this).attr("id"),
                "date": $(this).find('date').text(),
                "value": $(this).find('value').text(),
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
            EponaOperations.push(op);

            /* Stats */
            generateStats(op);

        });


        /*******************************************
         * Ajout des opérations dans la page       *
         * ****************************************/
        jTable = $('<table>');
        jThead = $('<thead>');
        jTr = $('<tr>');
        jTr.append("<th>id</th>");
        jTr.append("<th>Date</th>");
        jTr.append("<th>Crédit</th>");
        jTr.append("<th>Dédit</th>");
        jTr.append("<th>Labels</th>");
        jThead.append(jTr);
        jTable.append(jThead);
        for(var i=0; i<EponaOperations.length; i++) {
            var id = EponaOperations[i].id;
            var date = EponaOperations[i].date;
            var value = EponaOperations[i].value;
            var credit = (value > 0)?value:0;
            var debit = (value > 0)?0:value*(-1);
            var labels = '';
            for(var j=0; j<EponaOperations[i].labels.length; j++) {
                labels += EponaOperations[i].labels[j].name;
            }
            var tr = $("<tr>");
            tr.append("<td>"+id+"</td>");
            tr.append("<td>"+date+"</td>");
            tr.append("<td>"+credit+"</td>");
            tr.append("<td>"+debit+"</td>");
            tr.append("<td>"+labels+"</td>");
            jTable.append(tr);

        }
        var jDiv = $("<div id=\"liste-op\">");
        jDiv.append(jTable);
        //jDiv.draggable();
        //jDiv.resizable();
        $("#tabs-1").append(jDiv);


        /*******************************************
         * Ajout des Stats dans la page
         * ****************************************/
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


        return false;
    }

    $.ajax({
        type: "GET",
        url: "api_xml/api_operations.php",
        success: processXml,
        error : function (ret) {
            $("#log").append(ret);
                return false;
            }
    });

}





/******************************************************************************
 * FONCTION CATEGORIES
 *****************************************************************************/
function loadCategories()
{
    function processXml(xmlDoc)
    {
        jXml = $(xmlDoc);
        jXml.find("categorie").each( function() {
            var name = $(this).attr('name');
            var fatherId = $(this).attr('father_id');
            var id = $(this).attr('id');
            var cat = {
                'id': id,
                'name': name,
                'fatherId': fatherId
            };
            EponaCategories.push(cat);
            EponaCategoriesById[id] = cat;
            EponaCategoriesChildren[fatherId] = cat;
        });



        return false;
    }
    $.ajax({
        type: "GET",
        asyn: false,
        url: "api_xml/api_cat.php?op=get",
        success: processXml,
        error : function (ret) {
            $("#log").append(ret);
                return false;
            }
    });

}





