/*
 * @param
 * @return
 */
function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}

/* 
 * retourn l'objet Json nécéssaire à OFC 
 * @param tab tableau sous la form?
 * @return json
 */
function ofc_createLines(values) {
    /* max value */
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
        "title": { "text": "Pour Jie, que j'aimerais toujours" },
        "y_axis": { "min": 0, "max": maxy , "steps": steps}, 
        "x_axis": { "min": 0, "max": nbValues, "steps": 1 , "labels": { "steps": 2, "rotate": 270, "labels": ["Janvier 2008", "Février 2008"]}},
       "bg_colour": "#F9F9F9" 
    };
    return dataLines;
}




var data;

/**
 *
 *
 */
function open_flash_chart_data()
{
    values = new Array(1,2,33.4,50,12,56,53,48);
    data = ofc_createLines(values);
    return JSON.stringify(data);
    
}




function update(d) {
            var tmp = findSWF("ofc");
            tmp.load(JSON.stringify(d));
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
