/**
 * Created by sherahsmith on 2/18/14.
 * Uses the jQuery flot chart library to create bar charts based on
 * user-defined JSON data in
 */

 var setupBarCharts = function(){

    var formatData = function(set){

        var datapoints = _.map(set, function(row){
            return row.count;
        }),
        bardata,
        dateAgg = {};

        set.forEach(function(row){
            var d = row.date.slice(0,10);
            if(!dateAgg[moment(d).format('M')]){
                dateAgg[moment(d).format('M')] = parseInt(row.count);
            }else{
                dateAgg[moment(d).format('M')] += parseInt(row.count);
            }
        });

        bardata = _.map(dateAgg, function(n){
            return n;
        });

        return {
            labels : ["January","February","March","April","May","June","July","August","September","October","November","December"],
            datasets : [
                {
                    fillColor : "rgba(220,220,220,0.5)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    data : bardata
                }
            ]
        }

    };

    if($('.barArea').length){

        var ids = _.map($('.barArea'), function(num){
                return num.id;
            });

        ids.forEach(function(el_id){

            var dataSet = $.parseJSON($('#' + el_id).attr('data-chartdata')),
                //check the dataSet's format and proceed accordingly
                formattedData = formatData(dataSet),
                context = $( '#' + el_id.replace("Area","") ).get( 0 ).getContext( '2d' );

                new Chart(context).Bar(formattedData);

        });
    }


}
