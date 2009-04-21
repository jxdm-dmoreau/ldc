/* 
 * Jquery tools for Open Flash Chart
 *
 * @author cmoidavid@gmail.com
 */

/*
 * Array used to store all parameters of each graph
 */
function getData(data) {
    return data;
}

(function($){

    var ofc_options = new Array();


    /* 
     * retourn l'objet Json nécéssaire à OFC 
     * @param tab tableau sous la form?
     * @return json
     */
    function createLines(params) {
        /* retrieve parameters */
        var values = params.values;
        var title  = params.title;
        var labels = params.labels;
        /* calculs */
        var nbDim = values.length;
        var max = 0;
        for(var j = 0; j < nbDim; j++) {
            max = Math.max(max, Math.max.apply(Math, values[j]));
        }
        var nbValues = values[0].length;
        var height = 200;
        var pxByStep = 20;
        var nbStep = Math.ceil(height/pxByStep);
        var steps = Math.ceil(max/nbStep);
        var maxy = steps*Math.ceil(max/steps);

        var elements = [];
        for(var j = 0; j < nbDim; j++) {
            elements[j] = {
                "type": "line",
                "values": values[j],
                "colour": params.colours[j%params.colours.length],
            };
        }

        dataLines = {
            "elements": elements,
            "title": { "text": title },
            "y_axis": { "min": 0,
                        "max": maxy,
                        "steps": steps,
                        "colour":"#9BC3FF",
                        "grid-colour":"#EDEDED"
            }, 
            "x_axis": { "min": 0,
                        "max": nbValues,
                        "steps": 1 ,
                        "labels": { "steps": 2,
                                    "rotate": 270,
                                    "labels": labels
                        },
                        "colour":"#9BC3FF",
                        "grid-colour":"#EDEDED"
            },
           "bg_colour": "#F9F9F9",
          "tooltip": { "shadow": true, "stroke": 2, "colour": "#9BC3FF", "body": "{font-size: 10px; font-weight: bold; color: #000000;}" } 
        };
        return dataLines;
    };


    $.fn.ofc = function(method, options) {
        // Extend our default options with those provided.
        // Note that the first arg to extend is an empty object -
        // this is to keep from overriding our "defaults" object.
        var opts = $.extend({}, $.fn.ofc.defaults, options);
        return this.each(function() {
            // plugin code
            $this = $(this);
            id = $this.attr("id");
            if (method == "add") {
                // store the options
                ofc_options[id] = opts;
                var data = createLines(opts);
                data = JSON.stringify(data);
                $this.append(swfobject.embedSWF("open-flash-chart.swf", id, opts.width, opts.height, "9.0.0", "expressInstall.swf", {"get-data":"getData", "id": data}, {"wmode":"transparent"}));
            } else if (method == "update") {
                opts = $.extend({}, ofc_options[id], options); 
                var data = createLines(opts);
                data = JSON.stringify(data);
                var tmp = findSWF(id);
                tmp.load(data);
            }
        });
    };

    /* default options */
    $.fn.ofc.defaults = {
        "values" : new Array(0,0),
        "title"  : "title",
        "height" : 250,
        "width"  : 600,
        "colours": ["#FF0000", "#00FFOO", "#0000FF", "#FF9900"]

    };

})(jQuery);


