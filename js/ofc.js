function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}

 monaData = {
    "elements": [{ 
        "type": "bar_glass",
        "values": [0,2,5,6 ],
        "colour": "#AF99DF"
        }],
    "title": {
        "text": "Sun Mar 29 2009"
        }
 };


    var data = {
        "elements": [{
            "type": "line",
            "values": [0,2,3,5,2 ]
        }],
        "title": { "text": "Pour Jie, que j'aimerais toujours" },
        "y_axis": { "min": 0, "max": 8, "steps": 4 }, 
        "x_axis": { "min": 0, "max": 8, "steps": 1 },
       "bg_colour": "#F9F9F9" 
    };

/* 
 * retourn l'objet Json nécéssaire à OFC 
 * @param tab tableau sous la form?
 * @return json
 */
function ofc_createLines() {
    dataLines = {
        "elements": [{
            "type": "line",
            "values": [0,2,3,5,2]
        }],
        "title": { "text": "Pour Jie, que j'aimerais toujours" },
        "y_axis": { "min": 0, "max": 8, "steps": 4 }, 
        "x_axis": { "min": 0, "max": 8, "steps": 1 },
       "bg_colour": "#F9F9F9" 
    };
    return dataLines;
}



function update(d) {
            var tmp = findSWF("ofc");
            tmp.load(JSON.stringify(d));
}
var data;
function open_flash_chart_data()
{
    //data = ofc_createLines(NULL);
        return JSON.stringify(ofc_createLines());
}

/**
 * @param index as integer.
 *
 * Returns a CLONE of the chart with one of the elements removed
 */
function chart_remove_element(chart, index)
{
    
 //   global_showing_old_data = !global_showing_old_data;
    
    // clone the chart
    var modified_chart = {};
    jQuery.extend(modified_chart, chart);

    // remove the old data from the chart:
    var element = modified_chart.elements[1];
    var elements = new Array();
    var c=0;
    for(i=0; i<modified_chart.elements.length; i++)
    {
      if(i!=index)
      {
        elements[c] = modified_chart.elements[i];
        c++;
      }
    }
    modified_chart.elements = elements;
    return modified_chart;
}
