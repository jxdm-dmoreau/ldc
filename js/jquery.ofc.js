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
        var max = Math.max.apply(Math, values);
        var nbValues = values.length;
        var height = 200;
        var pxByStep = 20;
        var nbStep = Math.ceil(height/pxByStep);
        var steps = Math.ceil(max/nbStep);
        var maxy = steps*Math.ceil(max/steps);

        dataLines = {
            "elements": [{
                "type": "line",
                "values": values
            }],
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
           "bg_colour": "#F9F9F9" 
        };
        return dataLines;
    };

    $.fn.ofc = function(options) {
            // Extend our default options with those provided.
            // Note that the first arg to extend is an empty object -
            // this is to keep from overriding our "defaults" object.
            var opts = $.extend({}, $.fn.ofc.defaults, options);

            var data = createLines(opts);
            data = JSON.stringify(data);
            this.append(swfobject.embedSWF("open-flash-chart.swf", "ofc", opts.width, opts.height, "9.0.0", "expressInstall.swf", {"get-data":"getData", "id": data}, false));
            return this;
        };

    /* default options */
    $.fn.ofc.defaults = {
        "values" : new Array(0,0),
        "title"  : "title",
        "height" : 250,
        "width"  : 600
    };

    $.fn.update = function(options) {
        // Extend our default options with those provided.
        // Note that the first arg to extend is an empty object -
        // this is to keep from overriding our "defaults" object.
        var opts = $.extend({}, $.fn.ofc.defaults, options);

        var data = createLines(opts);
        data = JSON.stringify(data);
        var tmp = findSWF("ofc");
        tmp.load(data);
        return this;
    };

})(jQuery);


