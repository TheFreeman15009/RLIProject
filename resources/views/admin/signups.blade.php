<script src="/php-unserialize.js"></script>
@extends('layouts.app')
@section('content')

<div class="block text-gray-700 text-2xl font-bold p-10">Signup Details</div>
<div id="page1" class="flex w-1/2 sm:w-full md:w-full lg:w-full xl:w-full justify-center bg-white shadow-lg rounded px-4 pb-4" style="display: flex;">
    <table class="table-auto rounded-lg shadow-lg">
        <thead>
            <tr id="tableheadid" class="bg-gray-200">
            </tr>
        </thead>
        <tbody id="tablebodyid">  
        </tbody>
    </table>
</div>
<div id="page2" class="block w-1/2 sm:w-full md:w-full lg:w-full xl:w-full items-center bg-white shadow-lg rounded px-4 pb-4" style="display: none;">
    
</div>
<script>
    var data = <?php echo json_encode($data); ?>;
    var active_seasons = <?php echo json_encode($season); ?>;
    var tablebody = "";
    var tablehead = "<th class='px-4 py-2'>S No.</th><th class='px-4 py-2'>Name</th>";
    var eachrow = [];
    var serial_no = 0;

    for (var i = 0; i < active_seasons.length; ++i) {
        eachrow.push("<td class='border text-center px-4 py-2'> - </td>");
        tablehead += "<th class='px-4 py-2'>" + active_seasons[i] + "</th>";
    }
    
    document.getElementById("tableheadid").innerHTML = tablehead;
    function myFunction(index) { 
        
        var attendance = data[index].attendance ? "Yes" : "No";
        var preference1 = "";
        var preference2 = "";
        var preference3 = "";
        
        var assist = "";
        var assisttext = "<strong>Assist Used :</strong> None <br>";
        if (data[index].assists != "")
        {assist = PHPUnserialize.unserialize(data[index].assists);
        assisttext = "<strong>Assists Used : </strong><br>";}
        for (var i in assist){
            assisttext += assist[i] + "<br>";
        }
        for (var i = 0; i<data[index].season.constructors.length;i++){
            if (data[index].season.constructors[i].id == data[index].carprefrence.split(",")[0]){
                preference1 = data[index].season.constructors[i].name;
            }
            if (data[index].season.constructors[i].id == data[index].carprefrence.split(",")[1]){
                preference2 = data[index].season.constructors[i].name;
            }
            if (data[index].season.constructors[i].id == data[index].carprefrence.split(",")[2]){
                preference3 = data[index].season.constructors[i].name;
            }
        }
        document.getElementById("page2").innerHTML = "<div class='block w-full p-10'><div class='inline-block w-1/2 text-gray-700 text-5xl'>" + data[index].user.name + "</div>" + 
                                                     "<div class='inline-block w-1/4 text-gray-700 text-base pl-10'>Created : " + data[index].created_at + "</div></div>"+
                                                     "<div class='flex items-center'> <div class='inline-block w-1/3 text-gray-700 text-xl pl-10 pb-5'><strong>Driver number : </strong>" + data[index].drivernumber + "<br><br>" +
                                                     "<strong>Will be able to attend 75% attendance? : </strong>" + attendance + "<br><br>" + 
                                                     "<strong>Speedtest Link</strong> : <a class='text-blue-500 italic' target='_blank' href=" + data[index].speedtest + ">Show Result</a><br><br></div>" + 

                                                     "<div class='inline-block w-1/3 text-gray-700 text-xl pl-10 pb-5'><strong>Preference 1 : </strong>" + preference1 + "<br><br>" + 
                                                     "<strong>Preference 2 : </strong>" + preference2 + "<br><br>" + 
                                                     "<strong>Preference 3 : </strong>" + preference3 + "<br><br></div>" +

                                                     "<div class='inline-block w-1/3 h-full text-gray-700 text-xl pl-10 pb-5 capitalize'>" + assisttext + "<br><br></div></div>" + 

                                                     "<div class='block w-full text-gray-700 text-xl pl-10 pb-5'><div class='inline-block p-1 w-1/3'><a target='_blank' href='/storage/" + data[index].ttevidence1 + "'><img class='object-contain' src=/storage/"+ data[index].ttevidence1 +"></a></div>" + 
                                                     "<div class='inline-block p-1 w-1/3'><a target='_blank' href='/storage/" + data[index].ttevidence2 + "'><img class='object-contain' target='_blank' src=/storage/"+ data[index].ttevidence2 +"></a></div>" + 
                                                     "<div class='inline-block p-1 w-1/3'><a target='_blank' href='/storage/" + data[index].ttevidence3 + "'><img class='object-contain' target='_blank' src=/storage/"+ data[index].ttevidence3 +"></a></div>"
                                                      + "</div>" + 
                                                      
                                                      "<div class='block w-full text-gray-700 text-xl pl-10 pb-5'><div class='inline-block text-gray-700 text-xl text-center pb-5 w-1/3'><strong>Time Trial 1 : </strong>" + data[index].timetrial1 + "</div>" +
                                                      "<div class='inline-block text-gray-700 text-xl text-center pb-5 w-1/3'><strong>Time Trial 2 : </strong>" + data[index].timetrial2 + "</div>" +
                                                      "<div class='inline-block text-gray-700 text-xl text-center pb-5 w-1/3'><strong>Time Trial 3 : </strong>" + data[index].timetrial3 + "</div>" +
                                                       "</div>" + 
                                                       "<div class='flex w-full mt-5 content-center items-center justify-center'><button name='action' class='bg-purple-500 hover:bg-purple-600 text-white font-bold shadow-lg py-2 px-4 rounded focus:outline-none focus:shadow-outline' onclick='backFunction()'>Return to Table</button></div>"
                                                      ;
        document.getElementById("page1").style.display = "none";
        document.getElementById("page2").style.display = "block";
    } 

    function backFunction(){
        document.getElementById("page1").style.display = "flex";
        document.getElementById("page2").style.display = "none";
    }

    for (var i = 0;i<data.length;i++){
        for (var j = 0;j<data.length;j++){
            if (data[i].user.name == data[j].user.name){
                if (j < i){break;}
                else if (j == i){
                    serial_no += 1;
                    tablebody += "<tr><td class='border text-center px-4 py-2'>" + serial_no + "</td><td class='border text-center px-4 py-2'>" + data[i].user.name + "</td>";
                    
                    for (var k = 0;k<active_seasons.length;k++){
                        if (data[j].season.name == active_seasons[k]){
                            eachrow[k] = "<td class='border text-center px-4 py-2'> <button id='" + j + "' onclick='myFunction(this.id)'> View Details </button></td>";
                        }
                    }
                }
                else{
                    for (var k = 0;k<active_seasons.length;k++){
                        if (data[j].season.name == active_seasons[k]){
                            eachrow[k] = "<td class='border text-center px-4 py-2'> <button id='" + j + "' onclick='myFunction(this.id)'> View Details </button> </td>";
                            break;
                        }
                    }
                }
            }
            if (j == data.length - 1){
                for(var k = 0;k<active_seasons.length;k++){
                    tablebody += eachrow[k];
                    eachrow[k] = "<td class='border text-center px-4 py-2'> - </td>";
                }
                tablebody += "</tr>";
            }
        }
    }
    document.getElementById("tablebodyid").innerHTML = tablebody;
 
</script>




@endsection