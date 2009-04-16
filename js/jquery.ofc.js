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
    $.fn.extend({

/* ***************************************************************************
 * FONCTION principale
 * ***************************************************************************/
        Ofc: function(params) {

            var data = this.createLines(params);
            data = JSON.stringify(data);
            this.append(swfobject.embedSWF("open-flash-chart.swf", "ofc", params.width, params.height, "9.0.0", "expressInstall.swf", {"get-data":"getData", "id": data}, false));
            return this;
        },

        update: function(params) {
            var data = this.createLines(params);
            data = JSON.stringify(data);
            var tmp = findSWF("ofc");
            tmp.load(data);
            return this;
        },

        /* 
         * retourn l'objet Json nécéssaire à OFC 
         * @param tab tableau sous la form?
         * @return json
         */
        createLines: function(params) {
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
        }
        });
})(jQuery);


