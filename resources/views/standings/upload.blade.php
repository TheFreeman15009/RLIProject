@extends('layouts.app')
@section('content')

<style>
    .disable {
        opacity: 0.75;
        cursor: not-allowed;
        pointer-events: none;
    }

    .disableActionBtns {
        cursor: not-allowed;
        pointer-events: none;
        background-color: #a0aec0;
        border-color: #4a5568;
    }

    .tooltip {
        position: relative;
    }

    .tooltip::after {
        content: "Clear 'RACE TIME' errors";
        background-color: #333;
        border-radius: 10px;
        color: #ffffff;
        display: none;
        padding: 10px 15px;
        width: 100%;
        position: absolute;
        text-align: center;
        font-size: 14px;
        z-index: 999;
        top: 0;
        left: 50%;
        transform: translate(-50%, calc(-100% - 10px));
    }

    .tooltip::before {
        background-color: #333;
        content: ' ';
        display: none;
        position: absolute;
        width: 30px;
        height: 30px;
        z-index: 999;
        top: 0;
        left: 50%;
        transform: translate(-50%, calc(-100% - 5px)) rotate(45deg);
    }

    .tooltip:hover::after {
        display: block;
    }

    .tooltip:hover::before {
        display: block;
    }

    #reviewJSONTextArea:focus {
        outline: none !important;
        border: none !important;
    }

    #raceNumber::-webkit-outer-spin-button,
    #raceNumber::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #raceNumber {
        appearance: textfield;
        -moz-appearance: textfield;
    }

    input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    background-color: #fff;
    margin: 0;
    font: inherit;
    color: currentColor;
    width: 1rem;
    height: 1rem;
    border: 0.125rem solid currentColor;
    border-radius: 0.2rem;

    display: grid;
    place-content: center;
  }

  input[type="checkbox"]::before {
    content: "";
    width: 0.5rem;
    height: 0.5rem;
    transform: scale(0);
    transition: 120ms transform ease-in-out;
    box-shadow: inset 1rem 1rem white;
    /* box-shadow: inset 1rem 1rem blue; */
    border-radius: 0.1rem;
    background-color: CanvasText;
    
    transform-origin: center;
    clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
  }

  input[type="checkbox"]:checked::before {
    transform: scale(1);
  }

  input[type="checkbox"]:checked {
    background-color: blue;
    border-color: blue;
  }
</style>

<!-- Screen to show various JSON selection methods for upload -->
<div id="homeScreen" class="mx-64">
    <div id="missingTrackAlert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 py-2 px-4 my-4 rounded" role="alert">
        <p><strong>Track Key</strong> [track] is missing in the uploaded JSON</p>
    </div>
    
    <div id="missingResultsAlert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 py-2 px-4 my-4 rounded" role="alert">
        <p><strong>Results Key</strong> [results] is missing in the uploaded JSON</p>
    </div>

    <div id="JSONFormatErrorsAlert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 py-2 px-4 my-4 rounded" role="alert">
        <span><strong>JSON Format Error</strong></span>
        <span id="JSONFormatErrorMessage"></span>
        <span>
            [<a id="JSLintHrefLink" href="" class="text-blue-700" target="_blank"><strong><u>Click Here</u></strong></a> to validate JSON]
        </span>
    </div>

    <div id="positionKeyErrorAlert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 py-2 px-4 my-4 rounded" role="alert">
    </div>

    <div id="errorIncorrectRaceIDAlert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 py-2 px-4 my-4 rounded" role="alert">
        <p><strong>Race ID</strong> is not valid</p>
    </div>

    <p class="mt-5 text-4xl font-bold">Choose a starting point</p>
    <hr>

    <div class="flex flex-col my-8 justify-center gap-8">
        <!-- Upload an existing valid JSON -->
        <div class="flex flex-row items-center justify-between border-solid border-4 border-purple-500 px-10 py-8 rounded-md">
            <p class="text-3xl font-semibold text-gray-500">Edit a new valid JSON</p>
            <input id="fileInput" type="file" class="hidden"/>
            <label id="fileInputLabel" for="fileInput" class="w-40 text-center bg-purple-500 hover:bg-purple-700 text-white font-semibold py-2 px-4 border border-purple-700 rounded cursor-pointer">Upload JSON</label>
        </div>

        <!-- Import an uploaded race JSON -->
        <div class="flex flex-row items-center justify-between border-solid border-4 border-red-500 px-10 py-8 rounded-md">
            <p class="text-3xl font-semibold text-gray-500">Import uploaded race result</p>
            <div class="flex flex-row items-center justify-center">
                <p class="text-xl text-center font-semibold bg-red-400 px-3 py-1 rounded-l">Race ID </p>
                <input class="w-20 text-gray-800 text-lg text-center font-semibold border-2 py-1 border-red-400 rounded-r" type="number" id="raceNumber" min="1" value="1"/>
            </div>
            <button id="importRace" class="w-40 bg-red-500 hover:bg-red-700 text-white font-semibold py-2 px-4 border border-red-700 rounded">Import JSON</button>
        </div>

        <!-- Start from an empty JSON -->
        <div class="flex flex-row items-center justify-between border-solid border-4 border-orange-500 px-10 py-8 rounded-md">
            <p class="text-3xl font-semibold text-gray-500">Don't have a reference point?</p>
            <button id="scratch" class="w-40 bg-orange-500 hover:bg-orange-700 text-white font-semibold py-2 px-4 border border-orange-700 rounded">Empty JSON</button>
        </div>
    </div>

    <div class="flex flex-row align-items justify-center mt-6">
        <button id="moreOptionsBtn" class="px-2 py-1 bg-gray-500 hover:bg-gray-700 text-white font-semibold border border-gray-700 rounded">
            Additional options
            <i class="fa fa-arrow-down pl-1" aria-hidden="true"></i>
        </button>
    </div>
   
    <div id="moreOptionsContent" class="flex flex-row align-items justify-center mt-3 gap-5">
        <div class="flex flex-row items-center justify-center gap-2">
            <input class="cursor-pointer" type="checkbox" id="noPointsForFlCheck" />
            <label class="cursor-pointer" for="noPointsForFlCheck">No points for fastest lap</label>
        </div>

        <div class="flex flex-row items-center justify-center gap-2">
            <input class="cursor-pointer" type="checkbox" id="bypassConstructorsCheck" />
            <label class="cursor-pointer" for="bypassConstructorsCheck">Populate all constructors in dropdowns</label>
        </div>

        <div class="flex flex-row items-center justify-center gap-2">
            <input class="cursor-pointer" type="checkbox" id="uploadPartialResult" />
            <label class="cursor-pointer" for="uploadPartialResult">Upload partial result</label>
        </div>
    </div>

</div>

<!-- Screen to edit details in the uploaded JSON with validation checks -->
<div id="editScreen" class="mx-40">
    <div class="flex flex-row justify-between items-center">
        <p class="mt-5 text-4xl font-bold">Review all fields</p>
        
        <div class="flex flex-row gap-3">
            <button id="startOver" class="flex items-center justify-center gap-3 bg-red-500 hover:bg-red-700 text-white font-semibold px-4 py-2 border border-red-700 rounded">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Start Over
            </button>

            <button id="toggleControlsBtn" class="flex items-center justify-center gap-2 bg-blue-500 hover:bg-blue-700 text-white font-semibold px-4 py-2 border border-blue-700 rounded">
                <i class="fas fa-edit" aria-hidden="true"></i>
                Edit
            </button>
        </div>
    </div>
    <hr>
    
    <div id="showAdditionsInfoDiv" class="flex flex-row gap-3 mt-2">
        <p class="text-sm text-yellow-600 font-bold">Selected options:</p>
        <p id="showNoFlPointsInfo" class="text-sm text-gray-600 font-bold">• No points for fastest lap</p>
        <p id="showPopulateAllConstructorsInfo" class="text-sm text-gray-600 font-bold">• Populate all constructors in dropdowns</p>
        <p id="showUploadPartialResultInfo" class="text-sm text-gray-600 font-bold">• Upload partial result</p>
    </div>

    <div id="infoFullEditNotSupported" class="hidden w-1/3 bg-blue-100 border-l-4 text-sm border-blue-500 text-blue-700 py-1 px-3 mt-2 rounded" role="alert">
        <p>Full edit is <strong>not supported</strong> for partial result upload</p>
    </div>
    
    <div class="w-7/12 rounded mb-10 my-2">
        <p class="mt-5 text-xl text-gray-600 font-bold">Track and season</p>

        <!-- Shows a warning when round no. and season id combination is already present in DB -->
        <div id="warningSeasonRoundSameAsInitialAlert" class="hidden w-1/2 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert">
            <p><strong>SEASON</strong> and <strong>ROUND NUMBER</strong> should not be same as that of an exising race result</p>
        </div>

        <div id="errorSeasonAlert" class="hidden w-1/2 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
            <p><strong>SEASON</strong> [field is missing]</p>
        </div>

        <div id="errorRoundAlert" class="hidden w-1/2 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
            <p><strong>ROUND NUMBER</strong> [field must be a positive integer]</p>
        </div>

        <div id="errorCircuitAlert" class="hidden w-1/2 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
            <p><strong>CIRCUIT</strong> [field is missing]</p>
        </div>

        <div id="errorPointsAlert" class="hidden w-1/2 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
            <p><strong>POINTS SCHEME</strong> [invalid field]</p>
        </div>

        <table id="trackDetailsTable" class="w-full shadow-lg table-auto mt-1">
            <thead id="trackTableHeaders" class="hidden text-center bg-purple-500 text-white">
                <tr>
                    <th class="border w-32 font-bold rounded px-4 py-2">Season</th>
                    <th class="border w-32 font-bold rounded px-4 py-2 w-">Round Number</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Circuit</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Points Scheme</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">More Details</th>
                </tr>
            </thead>
            <tbody class="bg-white" id="trackTableBody">
            </tbody> 
        </table>
    </div>

    <div class="w-full rounded mb-8">
        <div class="flex flex-row items-center justify-between">
            <p class="mt-1 text-xl text-gray-600 font-bold">Finishing order</p>

            <div class="flex flex-row items-center justify-center gap-3">
                <div id="rowReorderBtnWrapper" class="cursor-pointer">
                    <button id="rowReorderToggleBtn" class="flex flex-row items-center justify-center gap-2 bg-white text-sm hover:bg-orange-500 text-orange-700 font-semibold hover:text-white py-1 px-2 border border-orange-500 hover:border-transparent rounded">
                        Show Row Reorder
                    </button>
                </div>
            
                <button id="undoToggleBtn" class="flex flex-row items-center justify-center gap-2 bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded">
                    Show Reset
                </button>

                <div id="raceTimeFormatBtnWrapper" class="cursor-pointer">
                    <button id="raceTimeFormatToggleBtn" class="flex flex-row items-center justify-center gap-2 bg-white text-sm hover:bg-red-500 text-red-700 font-semibold hover:text-white py-1 px-2 border border-red-500 hover:border-transparent rounded">
                        Show Intervals
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <div id="warningNoPosWithStatus1Alert" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert">
                <p>No row has 'Fastest Lap' <strong>STATUS</strong></p>
            </div>

            <div id="warningFlStatusNotMatchingAlert" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert"></div>

            <div id="warningFlBelowP10Alert" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert"></div>

            <div id="warningRaceTimeFirstPosNotAbsoluteAlert" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert">
                <p><strong>RACE TIME</strong> of Row <strong>1</strong> is not an absolute time in format: <strong>'1:06.006'</strong></p>
            </div>

            <div id="errorResultsAlerts"></div>
        </div>

        <table id="resultsDetailsTable" class="w-full table-auto shadow-lg mt-1">
            <thead id="resultsTableHeaders" class="hidden text-center bg-purple-500 text-white">
                <tr>
                    <th class="border w-32 font-bold rounded px-4 py-2">Position</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Driver</th>
                    <th class="hidden border w-32 font-bold rounded px-4 py-2">Driver ID</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Constructor</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Starting Grid</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Laps Completed</th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Fastest Lap</th>
                    <th class="border w-32 rounded px-4 py-2 raceTimeCol absoluteTime">
                        <div class="font-bold">
                            Race Time
                        </div>
                        <div id="raceTimeFormatText" class="font-light">
                            [Absolute]
                        </div>
                    </th>
                    <th class="border w-32 font-bold rounded px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white" id="resultsTableBody">
            </tbody>
        </table>

        <div id="addRemoveRowControls" class="hidden pt-3 justify-center items-center gap-5">
            <button id="addRow" class="bg-green-500 hover:bg-green-700 text-white font-semibold px-3 py-1 border border-green-700 rounded mr-2">+</button>
            
            <button id="removeRow" class="bg-red-500 hover:bg-red-700 text-white font-semibold px-3 py-1 border border-red-700 rounded">-</button>
        </div>
    </div>

    <div class="flex justify-center items-center gap-3">
        <button id="submitJSON" class="hidden w-24 bg-red-500 hover:bg-red-700 text-white font-semibold py-2 px-4 border border-red-700 rounded">Submit</button>

        <a id="reviewJSON" class="hidden bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 border border-blue-700 rounded cursor-pointer">Review JSON</a>
    </div>

    <div class="flex justify-center items-center mt-2">
        <div id="errorSubmitJSONAlert" class="hidden w-auto bg-red-100 border-l-4 border-r-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
            <p>Please clear all the<strong> ERRORS </strong> before submitting</p>
        </div>
    </div>

    <div id="pointsSelectionOverlay" class="bg-black bg-opacity-50 fixed inset-0 hidden justify-center items-start w-screen max-h-screen">
        <div class="my-6 bg-gray-200 rounded-lg w-3/4 max-h-screen py-2 px-3 shadow-xl">
            <div class="flex justify-between items-center border-b border-gray-400">
                <h4 class="p-2 text-lg md:text-xl font-bold">Points scheme details</h4>
                <svg class="w-6 h-7 cursor-pointer hover:bg-gray-400 rounded-sm cross" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="close-modal"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            
            <div>
                <p class="text-center text-red-600 text-md md:text-lg font-bold pt-3 pb-1">Choose the required points scheme</p>
            </div>
            
            <div class="justify-center px-4 py-2 w-full h-screen overflow-scroll" style="max-height: 80vh">
                <table id="pointsTable" class="table-auto">
                    <thead id="pointsTableHeaders" class="text-center bg-purple-500 text-white">
                    </thead>
                    <tbody class="bg-white" id="pointsTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="additionalDetailsInputOverlay" class="bg-black bg-opacity-50 fixed inset-0 hidden justify-center items-start w-screen max-h-screen">
        <div class="my-6 bg-gray-200 rounded-lg w-2/5 max-h-screen py-2 px-3 shadow-xl">
            <div class="flex justify-between items-center border-b border-gray-400">
                <h4 class="p-2 text-lg md:text-xl font-bold">Add more details</h4>
                <svg class="w-6 h-7 cursor-pointer hover:bg-gray-400 rounded-sm cross" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="close-modal"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>

            <div class="flex flex-row items-center justify-evenly my-3">
                <div class="flex flex-col items-center justify-center">
                    <p id="infoTitle" class="text-gray-600 text-md md:text-lg font-semibold mb-2"></p>

                    <table class="table-auto">
                        <thead class="text-center bg-gray-500 text-white">
                            <th id="infoHeader1" class="border rounded font-bold px-4 py-2"></th>
                            <th id="infoHeader2" class="border rounded font-bold px-4 py-2"></th>
                            <th id="infoHeader3" class="border rounded font-bold px-4 py-2"></th>
                        </thead>
                        
                        <tbody class="text-center bg-white">
                            <td id="infoValue1" class="border rounded disable px-4 py-2"></td>
                            <td id="infoValue2" class="border rounded disable px-4 py-2"></td>
                            <td id="infoValue3" class="border rounded disable px-4 py-2"></td>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-row items-center justify-evenly my-10">
                <div class="flex flex-col items-center justify-center">
                    <p class="text-gray-600 text-md md:text-lg font-semibold mb-2">Additional attributes</p>

                    <table id="additionalDetailsTable" class="table-auto">
                        <thead id="additionalDetailsTableHeaders" class="text-center bg-purple-500 text-white">
                            <th class="border rounded font-bold px-4 py-2">Attribute</th>
                            <th class="border rounded font-bold px-4 py-2">Value</th>
                        </thead>
                        
                        <tbody class="text-center bg-white" id="additionalDetailsTableBody">
                            <tr id="attributeRow" class="text-center">
                                <td id="attributeName" class="border rounded px-4 py-2 font-bold"></td>
                                <td class="flex justify-center items-center border rounded px-4 py-2">
                                    <input id="attributeValue" class="text-center font-bold" type="number" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="reviewJSONOverlay" class="bg-black bg-opacity-50 fixed inset-0 hidden justify-center items-start w-screen max-h-screen">
        <div class="my-6 bg-gray-200 rounded-lg w-2/5 max-h-screen py-2 px-3 shadow-xl">
            <div class="flex justify-between items-center border-b border-gray-400">
                <h4 class="p-2 text-lg md:text-xl font-bold">Review final JSON details</h4>
                <svg class="w-6 h-7 cursor-pointer hover:bg-gray-400 rounded-sm cross" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="close-modal"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>

            <div class="justify-center mt-2 px-4 py-2 w-full h-screen" style="max-height: 80vh">
                <textarea name="" id="reviewJSONTextArea" class="w-full h-full px-3 py-2 rounded-md" readonly></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Screen 3 to show server response and post result upload scenario -->
<div id="serverResponseScreen" class="mx-64">
    <div class="flex justify-center items-center my-10">
        <div id="onSuccess" class="flex flex-col items-center justify-center gap-3 w-full text-center text-lg bg-green-100 border-l-4 border-r-4 border-green-500 text-green-700 px-2 py-24 rounded" role="alert">
            <i class="text-6xl mb-2 fa fa-check-circle" aria-hidden="true"></i> 

            <p class="text-5xl font-bold mb-2">Success!</p>

            <div id="raceID" class="text-3xl mb-2"></div>

            <div class="flex flex-row justify-center items-center gap-5 mt-5">
                <button class="w-40 bg-red-500 hover:bg-red-700 text-white font-semibold py-2 px-4 border border-red-700 rounded homeBtn">Upload more</button>

                <a id="download" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 border border-blue-700 rounded cursor-pointer">Download result</a>
            </div>
        </div>
    </div>

    <div class="flex justify-center items-center my-10">
        <div id="onFailure" class="flex flex-col items-center justify-center gap-3 w-full text-center text-lg bg-red-100 border-l-4 border-r-4 border-red-500 text-red-700 px-2 py-24 rounded" role="alert">
            <i class="text-6xl mb-2 fa fa-times" aria-hidden="true"></i>
            
            <p class="text-5xl font-bold mb-2">Failed!</p>

            <p id="failureText" class="text-3xl mb-2"></p>

            <div class="flex flex-row justify-center items-center gap-5 mt-5">
                <button id="backToEditScreen" class="flex items-center justify-center gap-3 bg-blue-500 hover:bg-blue-700 text-white font-semibold px-4 py-2 border border-blue-700 rounded">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    Back
                </button>

                <button class="w-32 bg-red-500 hover:bg-red-700 text-white font-semibold py-2 px-4 border border-red-700 rounded homeBtn">Start Over</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        const season = <?php echo json_encode($season); ?>;
        const points = <?php echo json_encode($points); ?>;
        const tracks = <?php echo json_encode($tracks); ?>;
        const constructor = <?php echo json_encode($constructor); ?>;
        const driver = <?php echo json_encode($driver); ?>;
        const status = [
            {
                id: 0,
                value: 'Finished'
            },
            {
                id: 1,
                value: 'Fastest Lap'
            },
            {
                id: -2,
                value: 'DNF'
            },
            {
                id: -3,
                value: 'DSQ'
            }
        ];
        
        $('#editScreen').addClass('hidden');
        $('#serverResponseScreen').addClass('hidden');

        $('#moreOptionsContent').addClass('hidden');

        $('#moreOptionsBtn').click(function(event) {
            $('#moreOptionsContent').toggleClass('hidden');

            if($('#moreOptionsBtn').hasClass('open')) {
                $('#moreOptionsBtn').removeClass('bg-gray-700 hover:bg-gray-500 open');
                $('#moreOptionsBtn').addClass('bg-gray-500 hover:bg-gray-700');
                $('#moreOptionsBtn').html('Additional options <i class="fa fa-arrow-down pl-1" aria-hidden="true"></i>');
            }
            else {
                $('#moreOptionsBtn').removeClass('bg-gray-500 hover:bg-gray-700');
                $('#moreOptionsBtn').addClass('bg-gray-700 hover:bg-gray-500 open');
                $('#moreOptionsBtn').html('Additional options <i class="fa fa-arrow-up pl-1" aria-hidden="true"></i>');
            }
        })

        $('#undoToggleBtn').addClass('hidden');
        $('#rowReorderToggleBtn').addClass('hidden');

        $('#showAdditionsInfoDiv').addClass('hidden');
        $('#showNoFlPointsInfo').addClass('hidden');
        $('#showPopulateAllConstructorsInfo').addClass('hidden');
        $('#showUploadPartialResultInfo').addClass('hidden');
        
        $('#fileInput').change(function(event) {
            // Continue operation only when a file is uploaded
            if($('#fileInput')[0].files.length > 0) {
                let reader = new FileReader();
                
                reader.readAsText($('#fileInput')[0].files[0]);

                reader.addEventListener('load', function() {
                    let json = parseJSONWithErrorHandling(reader.result);

                    if(checkJsonKeys(json) && isAllPositionKeysPresentAndValidWithoutDuplicates(json.results)) {
                        json.results.sort((a,b) => a.position - b.position);

                        viewJSONData(json, season, points, tracks, constructor, driver, status);
                        
                        disableNumberInputsAndAddMoreBtns();
                        enableEditOnErrorAfterLoading(json, $('#uploadPartialResult').is(':checked'));
                    }
                });
                // Resetting the value to read the same file again
                $('#fileInput')[0].value = '';
            }
        });
        
        $('#importRace').click(function(event) {
            let raceNumber = $('#raceNumber').val();
            
            if(raceNumber === '' || raceNumber === '0') {
                $('#errorIncorrectRaceIDAlert').slideDown(500);
            }
            else {
                $('#errorIncorrectRaceIDAlert').slideUp(500);
                $('#importRace').html('<i class="fas fa-spinner fa-spin"></i>');

                fetchResultByIDAndPassForEdit(raceNumber, season, points, tracks, constructor, driver, status);
            }
        });

        $('#scratch').click(function(event) {
            let json = {
                track: {
                    circuit_id: 0,
                    season_id: 0,
                    round: 0,
                    points: 0
                },
                results: [
                    {
                        position: 1,
                        constructor_id: 0,
                        grid: 0,
                        stops: -1,
                        time: "",
                        fastestlaptime: "",
                        status: -2,
                        driver_id: null,
                        driver: ""
                    }
                ]
            };
            
            let isResultImportedOrFromScratch = -1;

            viewJSONData(json, season, points, tracks, constructor, driver, status, isResultImportedOrFromScratch);

            disableNumberInputsAndAddMoreBtns();
            enableEditOnErrorAfterLoading(json, $('#uploadPartialResult').is(':checked'));
        });

        $('.homeBtn').click(function(event) {
            location.reload(true);

            $('.homeBtn').html('<i class="fas fa-sync fa-spin"></i>');
        });

        $('#backToEditScreen').click(function(event) {
            $('#serverResponseScreen').addClass('hidden');
            $('#editScreen').removeClass('hidden');

            $('#submitJSON').html('Submit');
        });
    });

    function parseJSONWithErrorHandling(readerResult) {
        $('#JSONFormatErrorsAlert').slideUp(500);

        try {
            return JSON.parse(readerResult);
        }
        catch(err) {
            $('#JSONFormatErrorMessage').html(`[${err.message}]`);
            $('#JSLintHrefLink').attr('href', `https://jsonlint.com/?json=${readerResult}`);
            $('#JSONFormatErrorsAlert').slideDown(500);
        }
    }

    function checkJsonKeys(json) {
        $('#missingResultsAlert').slideUp(500);
        $('#missingTrackAlert').slideUp(500);
        $('#positionKeyErrorAlert').slideUp(500);

        if(json !== undefined) {
            if(json.hasOwnProperty('track') && json.hasOwnProperty('results')) {
                return 1;
            } else if(json.hasOwnProperty('track') == false) {
                $('#missingTrackAlert').slideDown(500);
            } else {
                $('#missingResultsAlert').slideDown(500);
            }
        }
    }

    function isAllPositionKeysPresentAndValidWithoutDuplicates(resultsArray) {
        let allKeysPresentAndValidWithoutDuplicate;
        let isJSONPartial = $('#uploadPartialResult').is(':checked');

        let positionStore = [];

        $('#positionKeyErrorAlert').slideUp(500);
        
        if(!isJSONPartial) {
            for(let i = 0; i < resultsArray.length; i++) {
                let pos = resultsArray[i].position;
                let isFraction = pos % 1;
    
                if(!resultsArray[i].hasOwnProperty('position')) {
                    $('#positionKeyErrorAlert').html(
                        `<p><strong>Position Key</strong> [position] is missing at index <strong>${i}</strong> of the 'results' array</p>`
                    );
                    $('#positionKeyErrorAlert').slideDown(500);
    
                    return allKeysPresentAndValidWithoutDuplicate = 0;
                } else if((isNaN(pos)) || (pos < 1) || (pos > resultsArray.length) || (isFraction != 0)) {
                    $('#positionKeyErrorAlert').html(
                        `<p><strong>Position Key</strong> [position] value at index <strong>${i}</strong> should be a positive integer less than the length of the 'results' array</p>`
                    );
                    $('#positionKeyErrorAlert').slideDown(500);
    
                    return allKeysPresentAndValidWithoutDuplicate = 0;
                } else if(positionStore.includes(pos)) {
                    $('#positionKeyErrorAlert').html(
                        `<p><strong>Position Key</strong> [position] value at index <strong>${i}</strong> is a duplicate value</p>`
                    );
                    $('#positionKeyErrorAlert').slideDown(500);
    
                    return allKeysPresentAndValidWithoutDuplicate = 0;
                }
    
                positionStore.push(pos);
            }
        }

        return allKeysPresentAndValidWithoutDuplicate = 1;
    }

    function viewJSONData(json, season, points, tracks, constructor, driver, status, isResultImportedOrFromScratch = 0) {
        $('#homeScreen').addClass('hidden');
        $('#editScreen').removeClass('hidden');

        let regexValidationStrings = {
            regexFastestLap: "^((\\d+\\:[0-5])|[0-5]?)\\d[.]\\d{3}$|^\\-$",
            regexTimeIsAbsolute: "^((\\d+\\:[0-5])|[0-5]?)\\d[.]\\d{3}$|^DNF$|^DSQ$|^DNS$|^\\+1 Lap$|^\\+[2-9][0-9]* Laps$|^\\-$",
            regexTimeIsInterval: "^(\\+|\\-)((\\d+\\:[0-5])|[0-5]?)\\d[.]\\d{3}$|^DNF$|^DSQ$|^DNS$|^\\+1 Lap$|^\\+[2-9][0-9]* Laps$|^\\-$",
            regexTimePosition1IsAbsolute: "^((\\d+\\:[0-5])|[0-5]?)\\d[.]\\d{3}$",
            regexTimeIsNotNumber: "^DNF$|^DSQ$|^DNS$|^\\+1 Lap$|^\\+[2-9][0-9]* Laps$|^\\-$"
        };

        let jsonTrackDetailsStore = {
            season: json.track.season_id,
            round: json.track.round
        };

        let jsonResultsDetailsStore = {
            driverName: [],
            driverID: [],
            grid: [],
            stops: [],
            raceTimeInAbsolutes: [],
            raceTimeInIntervals: [],
            originalStatusMinusUnitsPlace: [],
            status: [],
            aliasExactMatchKey: []
        };

        let additionalDetailsStore = {
            raceDistance: json.track.hasOwnProperty('distance') ? json.track.distance : '',
            resultsPoints: [],
            uploadedDriverID: []
        };

        let supportingVariables = {
            indexPosMap: [],
            usedStatusNumbers: [0, 1, -2, -3],
            currentPointsSchemeSelected: json.track.points,
            currentAddRowSelected: 0,
            fastestLapIndex: {
                current: 0, 
                previous: 0
            },
            availableDrivers: [],
            availableConstructors: constructor,
            additionalResultsPointsToAdd: [],
            isNoPointsForFastestLap: $('#noPointsForFlCheck').is(':checked'),
            isShowAllConstructorsChecked: $('#bypassConstructorsCheck').is(':checked'),
            isUploadPartialResultChecked: $('#uploadPartialResult').is(':checked'),
            isPointsUndefined: points.find(item => {return item.id === json.track.points}) === undefined ? true : false,
            isResultImported: {
                currentVal: isResultImportedOrFromScratch, 
                originalVal: isResultImportedOrFromScratch
            },
            isTimeInPosition1Absolute: new RegExp(regexValidationStrings.regexTimePosition1IsAbsolute),
            isTimeNotNumber: new RegExp(regexValidationStrings.regexTimeIsNotNumber)
        };

        // Checking presence of 'round' key
        if(!json.track.hasOwnProperty('round')) {
            json.track.round = '';
        }

        for(let i = 0; i < json.results.length; i++) {
            supportingVariables.indexPosMap.push(i + 1);
            
            checkPresenceOfResultsKeys(json, supportingVariables, i);
            pushValuesIntoStores(json, driver, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
        }
        
        if(supportingVariables.isNoPointsForFastestLap) {
            $('#showAdditionsInfoDiv').removeClass('hidden');
            $('#showNoFlPointsInfo').removeClass('hidden');
        }

        if(supportingVariables.isNoPointsForFastestLap && supportingVariables.isPointsUndefined) {
            alert("Warning: 'Points scheme' is not present in database. Begin editing by changing it to an appropirate value");
        }

        if(!supportingVariables.isTimeInPosition1Absolute.test(json.results[0].time)) {
            $('#warningRaceTimeFirstPosNotAbsoluteAlert').slideDown(500);
        }

        supportingVariables.availableDrivers = driver.filter(ele => !jsonResultsDetailsStore.driverID.includes(ele.id));

        if(!supportingVariables.isShowAllConstructorsChecked) {
            for(let i = 0; i < season.length; i++) {
                if(season[i].id === json.track.season_id) supportingVariables.availableConstructors = season[i].constructors;
            }
        }
        else {
            $('#showAdditionsInfoDiv').removeClass('hidden');
            $('#showPopulateAllConstructorsInfo').removeClass('hidden');
        }

        if(supportingVariables.isUploadPartialResultChecked) {
            $('#showAdditionsInfoDiv').removeClass('hidden');
            $('#showUploadPartialResultInfo').removeClass('hidden');

            $('#toggleControlsBtn').removeClass('bg-blue-500 hover:bg-blue-700 border-blue-700');
            $('#toggleControlsBtn').addClass('bg-gray-500 border-gray-700 cursor-not-allowed');

            $('#infoFullEditNotSupported').slideDown(500);
        }

        // Printing values of track key of json in table
        $('#trackTableHeaders').removeClass('hidden');
        updateTrackTable(json, season, tracks, points);
        
        // Printing values of results key of json in table
        $('#resultsTableHeaders').removeClass('hidden');
        updateResultsTable(json, points, driver, status, additionalDetailsStore, supportingVariables);

        if(!supportingVariables.isUploadPartialResultChecked) {
            // To enable normal function of 'edit' btn after import
            if(supportingVariables.isResultImported.currentVal === 1) {
                supportingVariables.isResultImported.currentVal = 0;
            }
    
            // Switch race time format to 'interval' on load when starting from scratch
            if(supportingVariables.isResultImported.originalVal === -1) {
                $('#raceTimeFormatText').html('[Interval]');
                
                for(let i = 0; i < json.results.length; i++) {
                    if((supportingVariables.indexPosMap[i] - 1) > 0) {
                        $(`#errorTimeAlert${i}`).html(
                            `<p>Row<strong> ${i+1}</strong> -<strong> RACE TIME</strong> [field must be in one of the following formats:<strong> '-'</strong>, <strong>'1:06.006'</strong>, <strong>'±10.324'</strong>, <strong>'±1:06.006'</strong>, <strong>+X Lap(s)</strong>, <strong>DNS</strong>, <strong>DNF</strong> or <strong>DSQ</strong>]</p>`
                        );
                    }
                }
    
                $('.raceTimeCol').removeClass('absoluteTime');
                $('#raceTimeFormatToggleBtn').html('Show Absolute Times');
            }

            openResultsMoreDetailsOverlay(json, additionalDetailsStore, supportingVariables);

            checkIfDriverNameIsExactMatch(jsonResultsDetailsStore, supportingVariables);
            checkDuplicateDiD(jsonResultsDetailsStore, supportingVariables);
            checkDuplicateStatus(jsonResultsDetailsStore, supportingVariables);
            
            for(let i = 0; i < json.results.length; i++) {            
                // Validate values in all 'results' table cells based on set rules
                checkAndMonitorResultsData(json, points, driver, supportingVariables.availableConstructors, status, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
                
                serialiseRowReorderControls(jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
            }
            
            checkDuplicateGrid(jsonResultsDetailsStore, supportingVariables);
            checkGridValueGreaterThanArraySize(jsonResultsDetailsStore, supportingVariables);
            checkGridValuesStartWith1(jsonResultsDetailsStore, supportingVariables);
            checkGridValuesForBreakInSequence(jsonResultsDetailsStore, supportingVariables);
            isAllGridValues0(jsonResultsDetailsStore, supportingVariables);
            isFastestLapPresentAndMatchingWithStatus(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
    
            addEventListenerToTrackUndoBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            
            for(let i = 0; i < json.results.length; i++) {
                addEventListenerToResultsUndoBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
            }
            
            addEventListenerOnAddNewRowBtn(json, season, points, driver, status, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            
            addEventListenerOnRemoveLastRowBtn(json, points, driver, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        }
        
        openTrackMoreDetailsOverlay(additionalDetailsStore);
        
        $('#attributeValue').change(function(event) {
            editMoreDetails(additionalDetailsStore, supportingVariables);
        });
        
        // Validate values in all 'track' table cells based on set rules
        checkAndMonitorTrackData(json, season, tracks, points, constructor, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        
        checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        
        addEventListenerToAllToggleBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);

        $('#startOver').click(function(event) {
            let choice = window.confirm('Are you sure you want to start over?')
            if(choice) {
                $('#startOver').html('<i class="fas fa-sync fa-spin"></i> Start Over');
                location.reload(true);
            }     
        });

        $('.cross').click(function(event) {
            $('#pointsSelectionOverlay').removeClass('flex');
            $('#additionalDetailsInputOverlay').removeClass('flex');
            $('#reviewJSONOverlay').removeClass('flex');
            
            $('#pointsSelectionOverlay').addClass('hidden');
            $('#additionalDetailsInputOverlay').addClass('hidden');
            $('#reviewJSONOverlay').addClass('hidden');
        });

        $('#reviewJSON').toggleClass('hidden');
        $('#submitJSON').toggleClass('hidden');

        $('#reviewJSON').click(function(event) {
            let newJson = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, 1);

            $('#reviewJSONTextArea').text(JSON.stringify(newJson, null, "\t"));

            $('#reviewJSONOverlay').removeClass('hidden');
            $('#reviewJSONOverlay').addClass('flex');
        });

        $('#submitJSON').click(function(event) {
            let newJson = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, 1);

            postJson(JSON.stringify(newJson), season);

            $('#submitJSON').html('<i class="fas fa-spinner fa-spin"></i>');
        });
    }

    function checkPresenceOfResultsKeys(json, supportingVariables, i) {
        if(!json.results[i].hasOwnProperty('grid') || json.results[i].grid === null) {
            json.results[i].grid = '';
        }

        if(!json.results[i].hasOwnProperty('stops') || json.results[i].stops === null) {
            json.results[i].stops = '';
        }

        if(!json.results[i].hasOwnProperty('fastestlaptime')) {
            json.results[i].fastestlaptime = '';
        }

        if(!json.results[i].hasOwnProperty('time')) {
            json.results[i].time = '';
        }

        supportingVariables.additionalResultsPointsToAdd[i] = json.results[i].hasOwnProperty('points') ? json.results[i].points : 0;
    }

    function pushValuesIntoStores(json, driver, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i) {
        let driverName = json.results[i].driver;
        let driverID = json.results[i].driver_id;
        let unitsPlace = parseInt(json.results[i].status % 10);
        let intervalValue = convertAbsoluteTimeToInterval(json.results[i].time, json.results[0].time);
        let isAliasExactMatchKeyPresent = json.results[i].hasOwnProperty('isExactMatch') ? true : false;
        let aliasExactMatchKeyValue = isAliasExactMatchKeyPresent ? json.results[i].isExactMatch : null;

        if(isNaN(driverID) || driverID > driver[driver.length - 1].id || driverID === '') {
            driverID = null;    
        }

        if(i === 0 || supportingVariables.isTimeNotNumber.test(json.results[i].time)) {
            intervalValue = json.results[i].time;
        }

        if(json.results[0].time === '-' && !supportingVariables.isTimeNotNumber.test(json.results[i].time)) {
            intervalValue = json.results[i].time;
        }
        
        // Pushing input values to respective stores
        jsonResultsDetailsStore.driverName.push(driverName);
        jsonResultsDetailsStore.driverID.push(driverID);
        jsonResultsDetailsStore.grid.push(json.results[i].grid);
        jsonResultsDetailsStore.stops.push(json.results[i].stops);
        jsonResultsDetailsStore.raceTimeInAbsolutes.push(json.results[i].time);
        jsonResultsDetailsStore.raceTimeInIntervals.push(intervalValue);
        jsonResultsDetailsStore.originalStatusMinusUnitsPlace.push(Math.abs(json.results[i].status - unitsPlace));
        
        json.results[i].status = unitsPlace;
        jsonResultsDetailsStore.status.push(json.results[i].status);
        
        jsonResultsDetailsStore.aliasExactMatchKey.push({
            isPresent: isAliasExactMatchKeyPresent,
            value: aliasExactMatchKeyValue
        });
        
        additionalDetailsStore.uploadedDriverID.push(driverID);
        additionalDetailsStore.resultsPoints.push(supportingVariables.additionalResultsPointsToAdd[i]);
    }

    function convertAbsoluteTimeToInterval(time, firstPosTime) {
        let firstPosTimeInSeconds = convertTimeFormatToSeconds(firstPosTime);
        let timeInSeconds = convertTimeFormatToSeconds(time);

        let interval = (timeInSeconds - firstPosTimeInSeconds).toFixed(3);

        if(Math.abs(+interval) > 60) {
            let minutes = Math.floor(interval / 60);
            let seconds = (interval % 60).toFixed(3);

            if(+interval < 0) {
                seconds = (Math.abs(+interval) % 60).toFixed(3);
            }
            
            if(parseFloat(+seconds) < 10){
                seconds = '0' + seconds;
            }
    
            interval = [minutes, seconds].join(':');
        }

        if(firstPosTimeInSeconds > timeInSeconds) {
            return `${interval}`;
        }
        else {
            return `+${interval}`;
        }
    }

    function convertTimeFormatToSeconds(fastestLapInMinutes) {
        let fastestLapSplit, minutes, seconds, fastestLapInSeconds;

        if(fastestLapInMinutes != null) {
            if(fastestLapInMinutes.includes(":")) {
                fastestLapSplit = fastestLapInMinutes.split(':');
                minutes = fastestLapSplit[0] * 60;
                seconds = +fastestLapSplit[1];
            }
            else {
                minutes = 0;
                seconds = +fastestLapInMinutes;
            }
        }
        fastestLapInSeconds = minutes + seconds;

        return fastestLapInSeconds;
    }

    function updateTrackTable(json, season, tracks, points) {
        let rowTrack = `<tr class="text-center">
                            <td class="border rounded py-2 px-1" id="trackBodySeason">
                                <div class="flex px-2 justify-center gap-2">
                                    <select id="seasonSelect" class="bg-gray-200 p-1 font-semibold leading-tight border border-gray-500 rounded hover:border-purple-600 hover:bg-purple-100 focus:outline-none focus:bg-white focus:border-gray-500 cursor-not-allowed selectInp trackSelectInp" disabled>                    
                                    </select>
                                    <button id="undoSeason" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="border rounded p-2" id="trackBodyRound">
                                <div class="flex px-2 justify-center gap-1">
                                    <input class="pl-6 w-16 text-center font-semibold numInp trackNumInp" type="number" id="inputRound" min="1" value="${json.track.round}"\>
                                    <button id="undoRound" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="border rounded py-2 px-1" id="trackBodyCircuit">
                                <div class="flex px-2 justify-center gap-2">
                                    <select id="tracksSelect" class="bg-gray-200 w-48 p-1 font-semibold leading-tight border border-gray-500 rounded hover:border-purple-600 hover:bg-purple-100 focus:outline-none focus:bg-white focus:border-gray-500 cursor-not-allowed selectInp trackSelectInp" disabled>                       
                                    </select>
                                    <button id="undoCircuit" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="border rounded py-2 px-1" id="trackBodyPoints">
                                <div class="flex px-2 justify-center gap-2">
                                    <button id="pointsBtn" type="button" class="px-5 font-semibold bg-gray-300 border border-gray-500 rounded cursor-not-allowed selectInp trackSelectInp" disabled>${json.track.points}</button>
                                    <button id="undoPoints" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <button id="addMoreTrack" class="bg-white text-sm hover:bg-green-700 text-green-500 hover:text-white font-semibold py-1 px-2 border border-green-500 hover:border-transparent rounded">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>`;
        $('#trackTableBody').append(rowTrack);

        let seasonCol = document.getElementById(`seasonSelect`);
        let circuitCol = document.getElementById(`tracksSelect`);
        let pointsCol = document.getElementById(`pointsSelect`);

        populateTrackDropdowns(json, season, tracks, points, seasonCol, circuitCol, pointsCol);
        populatePointsOverlay(points);
    }

    function populateTrackDropdowns(json, season, tracks, points, seasonCol, circuitCol, pointsCol) {
        let selectInpFields = {
            season: {
                data: season,
                upload: json.track.season_id,
                colId: seasonCol
            },
            track: {
                data: tracks,
                upload: json.track.circuit_id,
                colId: circuitCol
            }
        };

        Object.keys(selectInpFields).forEach((key) => {
            let select = "<option hidden selected value> -- Missing ID -- </option>";
            let dispValue;
            
            for(let i = 0; i < selectInpFields[key].data.length; i++) {
                dispValue = `${selectInpFields[key].data[i].name} (${selectInpFields[key].data[i].series.code.toUpperCase()})`;
                
                if(selectInpFields[key].data[i].id !== selectInpFields[key].upload) {
                    select += `<option value=${selectInpFields[key].data[i].id}>${dispValue}</option>`;
                }
                else {
                    select += `<option selected value=${selectInpFields[key].upload}>${dispValue}</option>`;
                }
            }

            selectInpFields[key].colId.innerHTML = select;
        });
    }

    function populatePointsOverlay(points) {
        let headerFill = "";

        for(let i = 0; i < points.length; i++) {
            headerFill += "<th class='border rounded font-bold px-4 py-2'> <input type='checkbox' class='transform scale-125 cursor-pointer' id='select"+ (i+1) +"'><p>" + points[i].id + "</p></th>";
        }

        let pointsHeader = `<tr><th class='border rounded font-bold px-4 py-2'>Pos</th>${headerFill}</tr>`;

        $('#pointsTableHeaders').append(pointsHeader);
        
        for(let i = 0; i < Object.keys(points[0]).length - 3; i++) {
            let columnFill = "";

            for(let j = 0; j < points.length; j++) {
                columnFill += "<td class='border text-center rounded py-1 px-3' contenteditable='false'>" + points[j]['P' + (i+1)] + "</td>";
            }

            let pointsRow = `<tr><td class='border text-center font-semibold rounded p-1' contenteditable='false'>P${i+1}</td>${columnFill}</tr>`;

            $('#pointsTableBody').append(pointsRow);
        }
    }

    function updateResultsTable(json, points, driver, status, additionalDetailsStore, supportingVariables, i = 0) {
        let upperLimit = i == 0 ? json.results.length - 1 : i;

        for(i; i <= upperLimit; i++) {
            let rowResult = `<tr class="text-center resultRow" id="resultsRow${i}">
                                <td class="border rounded p-2" id="resultsBodyPos${i}">
                                    <div class="flex px-2 justify-center gap-1">
                                        <button id="moveRowUp${i}" class="hidden bg-green-500 hover:bg-green-700 text-white font-semibold px-1 border border-green-700 rounded rowReorderBtn">
                                                <i class="text-sm fa fa-arrow-up"></i>
                                        </button>
                                        <button id="moveRowDown${i}" class="hidden bg-red-500 hover:bg-red-700 text-white font-semibold px-1 border border-red-700 rounded rowReorderBtn">
                                            <i class="text-sm fa fa-arrow-down"></i>
                                        </button>
                                        <input class="pl-6 w-16 text-center font-semibold numInp inputPos bg-white" type="number" id="inputPos${i}" min="1" value="${json.results[i].position}" disabled\>
                                    </div>
                                </td>
                                <td class="border rounded py-2 px-1" id="resultsBodyDriver${i}">
                                    <div class="flex px-2 justify-center gap-2">
                                        <select id='driverSelect${i}' class="bg-gray-200 font-semibold w-32 p-1 leading-tight border border-gray-500 rounded hover:border-purple-600 hover:bg-purple-100 focus:outline-none focus:bg-white focus:border-gray-500 cursor-not-allowed selectInp selectDriver" disabled>
                                        </select>
                                        <button id="undoDriver${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="hidden border rounded p-2">
                                    <input class="w-12 text-center font-semibold" type="text" id="inputDriver${i}" value="${json.results[i].driver_id}"\>
                                </td>
                                <td class="border rounded py-2 px-1" id="resultsBodyConstructor${i}">
                                    <div class="flex px-2 justify-center gap-2">
                                        <select id='constructorSelect${i}' class="bg-gray-200 font-semibold w-32 p-1 leading-tight border border-gray-500 rounded hover:border-purple-600 hover:bg-purple-100 focus:outline-none focus:bg-white focus:border-gray-500 cursor-not-allowed selectInp" disabled>                       
                                        </select>
                                        <button id="undoConstructor${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border rounded p-2" id="resultsBodyGrid${i}">
                                    <div class="flex px-2 justify-center gap-1">
                                        <input class="pl-3 w-16 text-center font-semibold numInp " type="number" id="inputGrid${i}" min="1" value="${json.results[i].grid}"\>
                                        <button id="undoGrid${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border rounded p-2" id="resultsBodyStops${i}">
                                    <div class="flex px-2 justify-center gap-1">
                                        <input class="pl-3 w-16 text-center font-semibold numInp" type="number" id="inputStops${i}" min="0" value="${json.results[i].stops}"\>
                                        <button id="undoStops${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border rounded p-2" id="resultsBodyFl${i}">
                                    <div class="flex px-2 justify-center gap-2">
                                        <input class="w-24 text-center font-semibold numInp" type="text" id="inputFl${i}" value="${json.results[i].fastestlaptime}"\>
                                        <button id="undoFl${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border rounded p-2" id="resultsBodyTime${i}">
                                    <div class="flex px-2 justify-center gap-2">
                                        <input class="w-24 text-center font-semibold numInp" type="text" id="inputTime${i}" value="${json.results[i].time}"\>
                                        <button id="undoTime${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border rounded py-2 px-1" id="resultsBodyStatus${i}">
                                    <div class="flex px-2 justify-around gap-2">
                                        <select id='statusSelect${i}' class="bg-gray-200 font-semibold w-24 p-1 leading-tight border border-gray-500 rounded hover:border-purple-600 hover:bg-purple-100 focus:outline-none focus:bg-white focus:border-gray-500 cursor-not-allowed selectInp selectStatus" disabled>                       
                                        </select>
                                        <button id="undoStatus${i}" class="hidden bg-white text-sm hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-2 border border-blue-500 hover:border-transparent rounded undo">
                                            <i class="fa fa-undo" aria-hidden="true"></i>
                                        </button>
                                        <button id="addMore${i}" class="bg-white text-sm hover:bg-green-700 text-green-500 hover:text-white font-semibold py-1 px-2 border border-green-500 hover:border-transparent rounded addMoreBtn">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                                
                            </tr>`;
            $('#resultsTableBody').append(rowResult);

            let resultsAlertsCreation = `<div id="warningRaceTimeFasterThanPrevPos${i}" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert"></div>

                                        <div id="warningRaceTimeNotMatchingStatus${i}" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert">
                                        </div>

                                        <div id="warningPositionClassifiedForPoints${i}" class="hidden w-3/4 bg-yellow-100 border-l-4 text-sm border-yellow-500 text-yellow-700 py-1 px-3 mb-2 rounded" role="alert">
                                        </div>

                                        <div id="infoDriverNameNotExactMatch${i}" class="hidden w-3/4 bg-blue-100 border-l-4 text-sm border-blue-500 text-blue-700 py-1 px-3 mb-2 rounded" role="alert">
                                        </div>
            
                                        <div id="errorPosAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> POSITION</strong> [field must be a positive integer]</p>
                                        </div>

                                        <div id="errorDriverAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> DRIVER</strong> [field is missing]</p>
                                        </div>

                                        <div id="errorConstructorAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> CONSTRUCTOR</strong> [field is missing]</p>
                                        </div>

                                        <div id="errorGridAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> STARTING GRID</strong> [field must be a positive integer]</p>
                                        </div>

                                        <div id="errorStopsAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> LAPS COMPLETED</strong> [field must be a non-negative integer]</p>
                                        </div>

                                        <div id="errorFlAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> FASTEST LAP</strong> [field must be in the following format: <strong>'1:06.006'</strong>]</p>
                                        </div>

                                        <div id="errorTimeAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> RACE TIME</strong> [field must be in one of the following formats:<strong> '-'</strong>, <strong>'1:06.006'</strong>, <strong>+X Lap(s)</strong>, <strong>DNS</strong>, <strong>DNF</strong> or <strong>DSQ</strong>]</p>
                                        </div>

                                        <div id="errorStatusAlert${i}" class="hidden w-3/4 bg-red-100 border-l-4 text-sm border-red-500 text-red-700 py-1 px-3 mb-2 rounded" role="alert">
                                            <p>Row<strong> ${i+1}</strong> -<strong> STATUS</strong> [invalid field]</p>
                                        </div>`;
            $('#errorResultsAlerts').append(resultsAlertsCreation);
            
            let driverCol = document.getElementById(`driverSelect${i}`);
            let constructorCol = document.getElementById(`constructorSelect${i}`);
            let statusCol = document.getElementById(`statusSelect${i}`);

            populateResultsDropdowns(json, driver, status, driverCol, constructorCol, statusCol, supportingVariables, i);
            reflectFastestLap(json.results[i].status, points, additionalDetailsStore, supportingVariables, i);
        }
    }

    function populateResultsDropdowns(json, driver, status, driverCol, constructorCol, statusCol, supportingVariables, i) {
        let selectInpFields = {
            driverID: {
                data: supportingVariables.availableDrivers,
                originalData: driver,
                upload: json.results[i].driver_id,
                colId: driverCol
            },
            constructorID: {
                data: supportingVariables.availableConstructors,
                upload: json.results[i].constructor_id,
                colId: constructorCol
            },
            status: {
                data: status,
                upload: json.results[i].status,
                colId: statusCol
            }
        }

        populateDriverDropdown(selectInpFields);
        populateConstructorDropdown(selectInpFields);
        populateStatusDropdown(selectInpFields);
    }

    function populateDriverDropdown(selectInpFields) {
        let driverSelect = "<option hidden selected value> -- Missing ID -- </option>";
        let driverDispVal;

        for(let y = 0; y < selectInpFields.driverID.originalData.length; y++) {
            if(selectInpFields.driverID.originalData[y].id === selectInpFields.driverID.upload) {
                driverDispVal = `${selectInpFields.driverID.originalData[y].name} (${selectInpFields.driverID.originalData[y].id})`;
                driverSelect += `<option selected value=${selectInpFields.driverID.originalData[y].id}>${driverDispVal}</option>`;
            }
        }

        // Populate driver dropdown without the selected driver from the available drivers
        for(let x = 0; x < selectInpFields.driverID.data.length; x++) {
            driverDispVal = `${selectInpFields.driverID.data[x].name} (${selectInpFields.driverID.data[x].id})`;         
            driverSelect += `<option value=${selectInpFields.driverID.data[x].id}>${driverDispVal}</option>`;
        }

        selectInpFields.driverID.colId.innerHTML = driverSelect;
    }

    function populateConstructorDropdown(selectInpFields) {
        let constructorSelect = "<option hidden selected value> -- Missing ID -- </option>";
        let constructorDispVal;
        
        for(let x = 0; x < selectInpFields.constructorID.data.length; x++) {
            constructorDispVal = `${selectInpFields.constructorID.data[x].name} (${selectInpFields.constructorID.data[x].id})`;

            if(selectInpFields.constructorID.data[x].id !== selectInpFields.constructorID.upload) {
                constructorSelect += `<option value=${selectInpFields.constructorID.data[x].id}>${constructorDispVal}</option>`;
            }
            else {
                constructorSelect += `<option selected value=${selectInpFields.constructorID.data[x].id}>${constructorDispVal}</option>`;
            }
        }

        selectInpFields.constructorID.colId.innerHTML = constructorSelect;
    }

    function populateStatusDropdown(selectInpFields) {
        let statusSelect = "<option hidden selected value> -- Missing Status -- </option>";
        let statusDispVal;

        for(let x = 0; x < selectInpFields.status.data.length; x++) {
            statusDispVal = selectInpFields.status.data[x].value;

            if(selectInpFields.status.data[x].id !== selectInpFields.status.upload) {
                statusSelect += `<option value=${selectInpFields.status.data[x].id}>${statusDispVal}</option>`;
            }
            else {
                statusSelect += `<option selected value=${selectInpFields.status.data[x].id}>${statusDispVal}</option>`;
            }
        }

        selectInpFields.status.colId.innerHTML = statusSelect;
    }

    function reflectFastestLap(selectedValue, points, additionalDetailsStore, supportingVariables, i) {
        if(points[supportingVariables.currentPointsSchemeSelected - 1] !== undefined) {
            supportingVariables.isPointsUndefined = false;
        }

        if(
            supportingVariables.isNoPointsForFastestLap && 
            !supportingVariables.isPointsUndefined && 
            supportingVariables.isResultImported.currentVal !== 1
        ) {
            if(
                selectedValue === 1 && 
                points[supportingVariables.currentPointsSchemeSelected - 1]['P' + supportingVariables.indexPosMap[i]] > 0
            ) {
                if(additionalDetailsStore.resultsPoints[i] == 0) {
                    alert(`'Row ${supportingVariables.indexPosMap[i]}' - Additional 'points' reset to -1 as it is a scoring position`);
                    
                    additionalDetailsStore.resultsPoints[supportingVariables.indexPosMap[i] - 1] = -1;
                    
                    $(`#addMore${i}`).removeClass('bg-white text-green-500');
                    $(`#addMore${i}`).addClass('bg-green-500 text-white');
                }
            }

            if(
                selectedValue === 1 && 
                points[supportingVariables.currentPointsSchemeSelected - 1]['P' + supportingVariables.indexPosMap[i]] == 0
            ) {
                if(additionalDetailsStore.resultsPoints[i] == 0) {
                    alert(`'Row ${supportingVariables.indexPosMap[i]}' - Additional 'points' reset to 0 as its a non scoring position`);
                    
                    additionalDetailsStore.resultsPoints[supportingVariables.indexPosMap[i] - 1] = 0;
                    
                    $(`#addMore${i}`).removeClass('bg-green-500 text-white');
                    $(`#addMore${i}`).addClass('bg-white text-green-500');
                }
            }
        }
        else if(!supportingVariables.isPointsUndefined) {
            if(selectedValue === 1 && supportingVariables.indexPosMap[i] > 10) {
                $('#warningFlBelowP10Alert').html(
                    `<p>'Fastest Lap' <strong>STATUS</strong> at Row <strong>${supportingVariables.indexPosMap[i]}</strong> [ensure -1 is added to additional attribute <strong>POINTS</strong>]</p>`
                );
                
                if(points[supportingVariables.currentPointsSchemeSelected - 1]['P' + supportingVariables.indexPosMap[i]] != 0) {
                    $(`#resultsBodyStatus${supportingVariables.indexPosMap[i] - 1}`).addClass('bg-yellow-600');
                    $('#warningFlBelowP10Alert').slideDown(500);
                }
                else {
                    $(`#resultsBodyStatus${supportingVariables.indexPosMap[i] - 1}`).removeClass('bg-yellow-600');
                    $('#warningFlBelowP10Alert').slideUp(500);
                }
            }
            else {
                $('#warningFlBelowP10Alert').slideUp(500);
            }
        }
        
        if(selectedValue === 1) {
            $(`#resultsRow${i}`).addClass('text-purple-600');
        }
        else {
            $(`#resultsRow${i}`).removeClass('text-purple-600');
        }
    }

    function openTrackMoreDetailsOverlay(additionalDetailsStore) {
        $('#addMoreTrack').click(function(event) {
            let seasonIndex = $('#seasonSelect')[0].selectedIndex;
            let circuitIndex = $('#tracksSelect')[0].selectedIndex;
            
            let selectedSeason = $('#seasonSelect')[0][seasonIndex].innerText;
            let selectedCircuit = $('#tracksSelect')[0][circuitIndex].innerText;
            let selectedPoints = $('#pointsBtn').html();

            setInfoDetails('Race details', 'Season', 'Track', 'Points', 'Distance', selectedSeason, selectedCircuit, selectedPoints, additionalDetailsStore.raceDistance);
        });

        if(additionalDetailsStore.raceDistance == 0) {
            additionalDetailsStore.raceDistance = '';
        }

        if(additionalDetailsStore.raceDistance != '') {
            $('#addMoreTrack').removeClass('bg-white text-green-500');
            $('#addMoreTrack').addClass('bg-green-500 text-white');
        }
    }

    function setInfoDetails(title, header1, header2, header3, infoName, infoValue1, infoValue2, infoValue3, attrValue) {
        $('#infoTitle').html(title);

        $('#infoHeader1').html(header1);
        $('#infoHeader2').html(header2);
        $('#infoHeader3').html(header3);

        $('#attributeName').html(infoName);

        $('#infoValue1').html(infoValue1);
        $('#infoValue2').html(infoValue2);
        $('#infoValue3').html(infoValue3);

        $('#attributeValue').val(attrValue);

        $('#additionalDetailsInputOverlay').removeClass('hidden');
        $('#additionalDetailsInputOverlay').addClass('flex');
    }

    function openResultsMoreDetailsOverlay(json, additionalDetailsStore, supportingVariables, i = 0) {
        let upperLimit = i === 0 ? json.results.length - 1 : i;

        for(let x = i; x <= upperLimit; x++) {
            $(`#addMore${x}`).click(function(event) {
                let index = parseInt($(this).closest('tr')[0].querySelector('.selectDriver').selectedIndex);
                let statusIndex = parseInt($(this).closest('tr')[0].querySelector('.selectStatus').selectedIndex);
                
                let currentPos = parseInt($(this).closest('tr')[0].querySelector('.inputPos').value);
                let currentDriver = $(this).closest('tr')[0].querySelector('.selectDriver')[index].innerText;
                let currentStatus = $(this).closest('tr')[0].querySelector('.selectStatus')[statusIndex].innerText;

                setInfoDetails('Current Driver', 'Position', 'Driver', 'Status', 'Points', currentPos, currentDriver, currentStatus, additionalDetailsStore.resultsPoints[supportingVariables.indexPosMap[x] - 1]);

                supportingVariables.currentAddRowSelected = x;
            });

            if(additionalDetailsStore.resultsPoints[x] != 0) {
                $(`#addMore${x}`).removeClass('bg-white text-green-500');
                $(`#addMore${x}`).addClass('bg-green-500 text-white');
            }
        }
    }

    function editMoreDetails(additionalDetailsStore, supportingVariables) {
        let selectedVal = $('#attributeValue').val();
        let attr = $('#attributeName').html();
        
        if(selectedVal !== '') {
            if(attr === 'Points') {
                additionalDetailsStore.resultsPoints[supportingVariables.indexPosMap[supportingVariables.currentAddRowSelected] - 1] = parseInt(selectedVal);
                
                if(selectedVal != 0) {
                    $(`#addMore${supportingVariables.currentAddRowSelected}`).removeClass('bg-white text-green-500');
                    $(`#addMore${supportingVariables.currentAddRowSelected}`).addClass('bg-green-500 text-white');
                }
                else {
                    $(`#addMore${supportingVariables.currentAddRowSelected}`).addClass('bg-white text-green-500');
                    $(`#addMore${supportingVariables.currentAddRowSelected}`).removeClass('bg-green-500 text-white');
                }
            }

            if(attr === 'Distance') {
                additionalDetailsStore.raceDistance = +selectedVal;

                if(selectedVal == 0) {
                    selectedVal = '';
                    additionalDetailsStore.raceDistance = '';
                }

                if(selectedVal != '') {
                    $('#addMoreTrack').removeClass('bg-white text-green-500');
                    $('#addMoreTrack').addClass('bg-green-500 text-white');
                }
                else {
                    $('#addMoreTrack').addClass('bg-white text-green-500');
                    $('#addMoreTrack').removeClass('bg-green-500 text-white');
                }
            }
        }
    }

    function checkAndMonitorTrackData(json, season, tracks, points, constructor, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        let dataToCheck = {
            season: {
                jsonValue: json.track.season_id,
                allValues: season,
                selectInpNode: '#seasonSelect',
                parentNode: '#trackBodySeason',
                alertNode: '#errorSeasonAlert',
                undoBtn: '#undoSeason'
            },
            round: {
                jsonValue: json.track.round,
                inpNode: '#inputRound',
                parentNode: '#trackBodyRound',
                alertNode: '#errorRoundAlert',
                undoBtn: '#undoRound'
            },
            track: {
                jsonValue: json.track.circuit_id,
                allValues: tracks,
                selectInpNode: '#tracksSelect',
                parentNode: '#trackBodyCircuit',
                alertNode: '#errorCircuitAlert',
                undoBtn: '#undoCircuit'
            },
            points: {
                jsonValue: json.track.points,
                allValues: points,
                btnNode: '#pointsBtn',
                parentNode: '#trackBodyPoints',
                alertNode: '#errorPointsAlert',
                undoBtn: '#undoPoints',
                overlayNode: '#pointsSelectionOverlay'
            }
        }

        checkIfSeasonAndRoundCombinationAreExisting(dataToCheck.season.jsonValue, dataToCheck.round.jsonValue, jsonTrackDetailsStore, supportingVariables);

        checkAndMonitorSeasonId(json, dataToCheck, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, supportingVariables);

        checkAndMonitorRoundNumber(json, dataToCheck, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, supportingVariables);

        checkAndMonitorCircuitId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);

        checkAndMonitorPointsScheme(json, points, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
    }

    function checkIfSeasonAndRoundCombinationAreExisting(jsonSeasonValue, jsonRoundValue, jsonTrackDetailsStore, supportingVariables) {
        if(supportingVariables.isResultImported.originalVal === 1) {
            let currentSeasonCheck = jsonTrackDetailsStore.season === jsonSeasonValue;
            let currentRoundCheck = jsonTrackDetailsStore.round === jsonRoundValue;

            if(currentSeasonCheck && currentRoundCheck) {
                $('#warningSeasonRoundSameAsInitialAlert').slideDown(500);
            }
            else {
                $('#warningSeasonRoundSameAsInitialAlert').slideUp(500);
            }
        }
    }

    function checkAndMonitorSeasonId(json, dataToCheck, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, supportingVariables) {
        let seasonValidCheck = dataToCheck.season.allValues.find(item => {return item.id === dataToCheck.season.jsonValue});
        
        if(seasonValidCheck === undefined) {
            $(dataToCheck.season.parentNode).addClass('bg-red-600');
            $(dataToCheck.season.alertNode).slideDown(500);
        }

        $(dataToCheck.season.selectInpNode).change(function(event) {
            let selectedValue = $(dataToCheck.season.selectInpNode).val();
            
            jsonTrackDetailsStore.season = parseInt(selectedValue);

            clearSelectWarning(dataToCheck.season.selectInpNode, dataToCheck.season.allValues, selectedValue, dataToCheck.season.parentNode, dataToCheck.season.alertNode);

            if(!supportingVariables.isShowAllConstructorsChecked) {
                for(let i = 0; i < dataToCheck.season.allValues.length; i++) {
                    if(dataToCheck.season.allValues[i].id === jsonTrackDetailsStore.season) {
                        supportingVariables.availableConstructors = dataToCheck.season.allValues[i].constructors;
                    }
                }

                repopulateConstructorResultsDropdowns(json, supportingVariables);
            }
            
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
            checkIfSeasonAndRoundCombinationAreExisting(dataToCheck.season.jsonValue, dataToCheck.round.jsonValue, jsonTrackDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.season.undoBtn, dataToCheck.season.selectInpNode, dataToCheck.season.jsonValue);
    }

    function clearSelectWarning(selectInpNode, allValues, selectedValue, parentNode, alertNode) {
        let validityCheck = allValues.find(item => {return item.id === selectedValue});

        if(validityCheck === undefined) {
            if(!selectedValue) {
                $(parentNode).removeClass('bg-yellow-600');
                $(parentNode).removeClass('bg-blue-600');
                $(parentNode).addClass('bg-red-600');
                $(alertNode).slideDown(500);
            }
            else {
                $(parentNode).removeClass('bg-red-600');
                $(alertNode).slideUp(500);
            }
        }
    }

    function repopulateConstructorResultsDropdowns(json, supportingVariables) {
        const e = new Event("change");

        for(let x = 0; x < json.results.length; x++) {
            let constructorSelect = "<option hidden selected value> -- Missing ID -- </option>";
            let constructorDispVal;
            let constructorCol = document.getElementById(`constructorSelect${x}`);

            for(let y = 0; y < supportingVariables.availableConstructors.length; y++) {
                constructorDispVal = `${supportingVariables.availableConstructors[y].name} (${supportingVariables.availableConstructors[y].id})`;

                if(supportingVariables.availableConstructors[y].id !== json.results[x].constructor_id) {
                    constructorSelect += `<option value=${supportingVariables.availableConstructors[y].id}>${constructorDispVal}</option>`;
                }
                else {
                    constructorSelect += `<option selected value=${supportingVariables.availableConstructors[y].id}>${constructorDispVal}</option>`;
                }
            }
            constructorCol.innerHTML = constructorSelect;

            constructorCol.dispatchEvent(e);
        }
    }

    function checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables) {
        let postStatus = {
            track: 1,
            results: 1
        }

        checkEachCellValueForError(json, postStatus);

        if(
            isValidTimeFormat(json, regexValidationStrings, jsonResultsDetailsStore) && 
            (postStatus.track === 1) && 
            (postStatus.results === 1)
        ) {
            $('#errorSubmitJSONAlert').slideUp(500);
            $('#submitJSON').removeClass('disableActionBtns');
            $('#reviewJSON').removeClass('disableActionBtns');
        }
        else {
            $('#submitJSON').addClass('disableActionBtns');
            $('#reviewJSON').addClass('disableActionBtns');
            $('#errorSubmitJSONAlert').html('<p>Please clear all the <strong>ERRORS</strong></p>');
            $('#errorSubmitJSONAlert').slideDown(500);
        }
    }

    function checkEachCellValueForError(json, postStatus) {
        let trackOuterDivs = ['#trackBodySeason', '#trackBodyRound', '#trackBodyCircuit', '#trackBodyPoints'];

        for(let i = 0; i < trackOuterDivs.length; i++) {
            postStatus.track -= checkForError(trackOuterDivs[i]);
        }

        for(let i = 0; i < json.results.length; i++){
            let resultsOuterDivs = [`#resultsBodyPos${i}`, `#resultsBodyDriver${i}`, `#resultsBodyConstructor${i}`, `#resultsBodyGrid${i}`, `#resultsBodyStops${i}`, `#resultsBodyFl${i}`, `#resultsBodyTime${i}`, `#resultsBodyStatus${i}`];
            
            for(let j = 0; j < resultsOuterDivs.length; j++) {
                postStatus.results -= checkForError(resultsOuterDivs[j]);
            }
        }
    }

    function checkForError(value) {
        let flag = 0;
        
        if($(value).hasClass('bg-red-600')) {
            flag = 1;
        }

        return flag;
    }

    function isValidTimeFormat(json, regexValidationStrings, jsonResultsDetailsStore) {
        let timeCheckFastestLap = new RegExp(regexValidationStrings.regexFastestLap);
        let timeCheckAbsolute = new RegExp(regexValidationStrings.regexTimeIsAbsolute);
        let timeCheckInterval = new RegExp(regexValidationStrings.regexTimeIsInterval);
        let postStatus = 1;
        
        for(let i = 0; i < json.results.length; i++) {
            if (i === 0) {
                if (!(timeCheckAbsolute.test(jsonResultsDetailsStore.raceTimeInIntervals[0]))) {
                    postStatus = 0;
                }
            } else {
                const timeToTest = $('.raceTimeCol').hasClass('absoluteTime') ? json.results[i].time : jsonResultsDetailsStore.raceTimeInIntervals[i];

                const isValidTime = $('.raceTimeCol').hasClass('absoluteTime') ? timeCheckAbsolute.test(timeToTest) : timeCheckInterval.test(timeToTest);
                
                if (!isValidTime) {
                    postStatus = 0;
                }
            }
        }
        return postStatus;
    }

    function resetField(undoBtn, node, jsonValue) {
        $(undoBtn).click(function(event) {
            $(node).val(jsonValue);

            if($(node).val() === null) {
                $(node).prop('selectedIndex', 0);
            }
            
            const e = new Event("change");
            const element = document.querySelector(node);
            element.dispatchEvent(e);
        });
    }

    function checkAndMonitorRoundNumber(json, dataToCheck, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, supportingVariables) {
        inputFormatCheck(dataToCheck.round.jsonValue, dataToCheck.round.parentNode, dataToCheck.round.inpNode, dataToCheck.round.alertNode);

        $(dataToCheck.round.inpNode).change(function(event) {
            let selectedRoundVal = $(dataToCheck.round.inpNode).val();

            jsonTrackDetailsStore.round = parseInt(selectedRoundVal);

            inputFormatCheck(parseInt(selectedRoundVal), dataToCheck.round.parentNode, dataToCheck.round.inpNode, dataToCheck.round.alertNode);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
            checkIfSeasonAndRoundCombinationAreExisting(dataToCheck.season.jsonValue, dataToCheck.round.jsonValue, jsonTrackDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.round.undoBtn, dataToCheck.round.inpNode, dataToCheck.round.jsonValue);
    }

    function inputFormatCheck(inputValue, parentNode, inpNode, alertNode, minVal = 1) {
        let isFraction = inputValue % 1;

        if(isNaN(inputValue) || (inputValue < minVal) || (inputValue == '.') || (isFraction != 0)) {
            $(parentNode).addClass('bg-red-600');
            $(inpNode).addClass('bg-red-600');
            $(inpNode).addClass('font-bold');
            $(inpNode).addClass('text-white');
            $(alertNode).slideDown(500);
        } else {
            $(parentNode).removeClass('bg-red-600');
            $(inpNode).removeClass('bg-red-600');

            if(!$(parentNode).hasClass('bg-yellow-600')) {
                $(inpNode).removeClass('font-bold');
                $(inpNode).removeClass('text-white');
            }

            $(alertNode).slideUp(500);
        }
    }

    function checkAndMonitorCircuitId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables) {
        let trackValidCheck = dataToCheck.track.allValues.find(item => {return item.id === dataToCheck.track.jsonValue});

        if(trackValidCheck === undefined) {
            $(dataToCheck.track.parentNode).addClass('bg-red-600');
            $(dataToCheck.track.alertNode).slideDown(500);
        }

        $(dataToCheck.track.selectInpNode).change(function(event) {
            let selectedValue = $(dataToCheck.track.selectInpNode).val();

            clearSelectWarning(dataToCheck.track.selectInpNode, dataToCheck.track.allValues, selectedValue, dataToCheck.track.parentNode, dataToCheck.track.alertNode);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.track.undoBtn, dataToCheck.track.selectInpNode, dataToCheck.track.jsonValue);
    }

    function checkAndMonitorPointsScheme(json, points, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        let pointsValidCheck = dataToCheck.points.allValues.find(item => {return item.id === dataToCheck.points.jsonValue});

        if(pointsValidCheck === undefined) {
            $(dataToCheck.points.parentNode).addClass('bg-red-600');
            $(dataToCheck.points.alertNode).slideDown(500);
            $(dataToCheck.points.btnNode).html('0')
        }

        updatePointsSelection(json, points, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, dataToCheck.points.btnNode, dataToCheck.points.overlayNode, dataToCheck.points.parentNode, dataToCheck.points.alertNode);

        $(dataToCheck.points.undoBtn).click(function(event) {
            if(pointsValidCheck == undefined) {
                $(dataToCheck.points.btnNode).html('0');
                $('input:checkbox').not(this).prop('checked', false);

                $(dataToCheck.points.parentNode).addClass('bg-red-600');
                $(dataToCheck.points.alertNode).slideDown(500);
                
                checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);

            } else {
                $(dataToCheck.points.btnNode).html(dataToCheck.points.jsonValue);
                $('input:checkbox').not(this).prop('checked', false);
            }
        });
    }

    function updatePointsSelection(json, points, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, btnNode, overlayNode, parentNode, alertNode) {
        $(btnNode).click(function(event) {
            $(overlayNode).removeClass('hidden');
            $(overlayNode).addClass('flex');

            let selectIndex = $(btnNode).html();
            $(`#select${selectIndex}`).prop('checked', true);
        });

        for(let i = 0; i < points.length; i++) {
            $(`#select${i+1}`).click(function(event) {
                $('input:checkbox').not(this).prop('checked', false);
                $(btnNode).html(i + 1);

                $(parentNode).removeClass('bg-red-600');
                $(alertNode).slideUp(500);
                $(overlayNode).removeClass('flex');
                $(overlayNode).addClass('hidden');

                supportingVariables.currentPointsSchemeSelected = i + 1;
                supportingVariables.isPointsUndefined = false;

                let selectedValue, flAtIndex;
                for(let x = 0; x < json.results.length; x++) {  
                    if(parseInt($(`#statusSelect${x}`).val()) === 1) {
                        selectedValue = parseInt($(`#statusSelect${x}`).val());
                        flAtIndex = x;
                    }
                }

                reflectFastestLap(selectedValue, points, additionalDetailsStore, supportingVariables, flAtIndex);
                checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
            });
        }
    }

    function checkIfDriverNameIsExactMatch(jsonResultsDetailsStore, supportingVariables) {
        for(let i = 0; i < jsonResultsDetailsStore.aliasExactMatchKey.length; i++) {
            if(jsonResultsDetailsStore.aliasExactMatchKey[i].isPresent && jsonResultsDetailsStore.aliasExactMatchKey[i].value === false) {
                $(`#resultsBodyDriver${i}`).addClass('bg-blue-600');
                $(`#infoDriverNameNotExactMatch${supportingVariables.indexPosMap[i] - 1}`).html(
                    `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> with <strong>DRIVER</strong> name<strong> ${jsonResultsDetailsStore.driverName[i]}</strong> does not match with the names associated with ID: ${jsonResultsDetailsStore.driverID[i]}</p>`
                );
                $(`#infoDriverNameNotExactMatch${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
            }
            else {
                $(`#resultsBodyDriver${i}`).removeClass('bg-blue-600');
                $(`#infoDriverNameNotExactMatch${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
            }
        }
    }

    function checkDuplicateDiD(jsonResultsDetailsStore, supportingVariables) {
        let duplicateDiD = findDuplicateIds(jsonResultsDetailsStore.driverID);

        for(let i = 0; i < jsonResultsDetailsStore.driverID.length; i++) {
            if(
                !isNaN(jsonResultsDetailsStore.driverID[i]) && 
                jsonResultsDetailsStore.driverID[i] != null && 
                jsonResultsDetailsStore.driverID[i] != 0
            ) {
                let j = 0;
                for(j; j < duplicateDiD.length; j++) {
                    if(duplicateDiD[j] === jsonResultsDetailsStore.driverID[i]) {
                        break;
                    }
                }
                
                if(j === duplicateDiD.length) {
                    $(`#resultsBodyDriver${i}`).removeClass('bg-red-600');
                    $(`#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
                    $(`#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> DRIVER</strong> [field is missing]</p>`
                    );
                }
                else {
                    $(`#resultsBodyDriver${i}`).removeClass('bg-yellow-600');
                    $(`#resultsBodyDriver${i}`).removeClass('bg-blue-600');
                    $(`#resultsBodyDriver${i}`).addClass('bg-red-600');
                    $(`#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> DRIVER</strong> [field is a duplicate value]</p>`
                    );
                    $(`#infoDriverNameNotExactMatch${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
                    $(`#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
                }
            }
            else {
                $(`#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                    `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> DRIVER</strong> [field is missing]</p>`
                );
            }
        }
    }
    
    function findDuplicateIds(array) {
        let uniqueId = new Set(array);

        let filteredEle = array.filter(ele => {
            if(uniqueId.has(ele)) {
                uniqueId.delete(ele)
            }
            else {
                return ele;
            }
        })
        
        return [...new Set(filteredEle)];
    }

    function checkDuplicateStatus(jsonResultsDetailsStore, supportingVariables) {
        let duplicateStatus = findDuplicateIds(jsonResultsDetailsStore.status);

        for(let i = 0; i < jsonResultsDetailsStore.status.length; i++) {
            if(
                jsonResultsDetailsStore.status[i] != null && 
                !isNaN(jsonResultsDetailsStore.status[i]) && 
                supportingVariables.usedStatusNumbers.includes(jsonResultsDetailsStore.status[i])
            ) {
                let j = 0;
                for(j; j < duplicateStatus.length; j++) {
                    if(duplicateStatus[j] === jsonResultsDetailsStore.status[i] && duplicateStatus[j] === 1) {
                        break;
                    }
                }
                
                if(j === duplicateStatus.length) {
                    $(`#resultsBodyStatus${i}`).removeClass('bg-red-600');
                    $(`#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STATUS</strong> [invalid field]</p>`
                    );
                    $(`#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
                }
                else {
                    $(`#resultsBodyStatus${i}`).removeClass('bg-yellow-600');
                    $(`#resultsBodyStatus${i}`).addClass('bg-red-600');
                    $(`#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STATUS</strong> [field is a duplicate fastest lap value]</p>`
                    );
                    $(`#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
                }
            }
            else {
                $(`#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                    `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STATUS</strong> [invalid field]</p>`
                );
            }
        } 
    }

    function checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, i) {
        let currentRaceTime = json.results[supportingVariables.indexPosMap[i] - 1].time;
        let cutoffLaps = Math.ceil(jsonResultsDetailsStore.stops[0] * 0.75);

        if(currentRaceTime === 'DNF') {
            if(
                jsonResultsDetailsStore.status[i] !== -2 && 
                (jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 1] < cutoffLaps ||
                cutoffLaps == 0)
            ) {
                statusIsNotDNFForUnclassifiedPosition(supportingVariables, i);
            }
            else if(
                jsonResultsDetailsStore.status[i] === -2 && 
                cutoffLaps > 0 &&
                jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 1] >= cutoffLaps
            ) {
                statusIsDNFForClassifiedPosition(jsonResultsDetailsStore, supportingVariables, i);
            }
            else {
                $(`#resultsBodyStatus${i}`).removeClass('bg-yellow-600');
                $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
                $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
            }
        }
        else if(currentRaceTime === 'DSQ') {
            if(jsonResultsDetailsStore.status[i] !== -3) {
                statusIsNotDSQ(supportingVariables, i);
            }
            else {
                $(`#resultsBodyStatus${i}`).removeClass('bg-yellow-600');
                $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
            }
        }
        else if(
            jsonResultsDetailsStore.status[i] === -2 && 
            cutoffLaps > 0 &&
            jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 1] >= cutoffLaps
        ) {
            statusIsDNFForClassifiedPosition(jsonResultsDetailsStore, supportingVariables, i);
        }
        else {
            $(`#resultsBodyStatus${i}`).removeClass('bg-yellow-600');
            $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
            $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
        }
    }

    function statusIsNotDNFForUnclassifiedPosition(supportingVariables, i) {
        $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).html(
            `Row <strong>${supportingVariables.indexPosMap[i]}</strong> with race time of <strong>DNF</strong> does not have 'DNF' <strong>STATUS</strong>`
        );

        if(!$(`#resultsBodyStatus${i}`).hasClass('bg-red-600')) {
            $(`#resultsBodyStatus${i}`).addClass('bg-yellow-600');
        }

        $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
        $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
    }

    function statusIsDNFForClassifiedPosition(jsonResultsDetailsStore, supportingVariables, i) {
        $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).html(
            `Row <strong>${supportingVariables.indexPosMap[i]}</strong> with race time of <strong>DNF</strong> having completed <strong>${jsonResultsDetailsStore.stops[i]} LAPS</strong> should not have 'DNF' <strong>STATUS</strong>`
        );

        if(!$(`#resultsBodyStatus${i}`).hasClass('bg-red-600')) {
            $(`#resultsBodyStatus${i}`).addClass('bg-yellow-600');
        }

        $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
        $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
    }

    function statusIsNotDSQ(supportingVariables, i) {
        $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).html(
            `Row <strong>${supportingVariables.indexPosMap[i]}</strong> with race time of <strong>DSQ</strong> does not have 'DSQ' <strong>STATUS</strong>`
        );

        if(!$(`#resultsBodyStatus${i}`).hasClass('bg-red-600')) {
            $(`#resultsBodyStatus${i}`).addClass('bg-yellow-600');
        }

        $(`#warningPositionClassifiedForPoints${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
        $(`#warningRaceTimeNotMatchingStatus${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
    }

    function checkAndMonitorResultsData(json, points, driver, constructor, status, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i) {
        let dataToCheck = {
            driverID: {
                jsonValue: json.results[i].driver_id,
                allValues: driver,
                selectInpNode: `#driverSelect${i}`,
                parentNode: `#resultsBodyDriver${i}`,
                alertNode: `#errorDriverAlert${i}`,
                warningNode: `#infoDriverNameNotExactMatch${i}`,
                undoBtn: `#undoDriver${i}`
            },
            constructorID: {
                jsonValue: json.results[i].constructor_id,
                allValues: constructor,
                selectInpNode: `#constructorSelect${i}`,
                parentNode: `#resultsBodyConstructor${i}`,
                alertNode: `#errorConstructorAlert${i}`,
                undoBtn: `#undoConstructor${i}`
            },
            grid: {
                jsonValue: json.results[i].grid,
                inpNode: `#inputGrid${i}`,
                parentNode: `#resultsBodyGrid${i}`,
                alertNode: `#errorGridAlert${i}`,
                undoBtn: `#undoGrid${i}`
            },
            stops: {
                jsonValue: json.results[i].stops,
                inpNode: `#inputStops${i}`,
                parentNode: `#resultsBodyStops${i}`,
                alertNode: `#errorStopsAlert${i}`,
                undoBtn: `#undoStops${i}`
            },
            fastestLap: {
                jsonValue: json.results[i].fastestlaptime,
                inpNode: `#inputFl${i}`,
                parentNode: `#resultsBodyFl${i}`,
                alertNode: `#errorFlAlert${i}`,
                undoBtn: `#undoFl${i}`
            },
            time: {
                jsonValue: json.results[i].time,
                inpNode: `#inputTime${i}`,
                parentNode: `#resultsBodyTime${i}`,
                alertNode: `#errorTimeAlert${i}`,
                undoBtn: `#undoTime${i}`
            },
            status: {
                jsonValue: json.results[i].status,
                allValues: status,
                selectInpNode: `#statusSelect${i}`,
                parentNode: `#resultsBodyStatus${i}`,
                alertNode: `#errorStatusAlert${i}`,
                undoBtn: `#undoStatus${i}`
            }
        }

        checkAndMonitorDriverId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);

        checkAndMonitorConstructorId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);
        
        checkAndMonitorStartingGrid(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);

        checkAndMonitorLapsCompleted(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);
        
        checkAndMonitorFastestLap(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);
        
        checkAndMonitorRaceTime(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i);
        
        checkAndMonitorStatus(json, points, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
    }

    function checkAndMonitorDriverId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        let driverIDValidCheck = dataToCheck.driverID.allValues.find(item => {return item.id === dataToCheck.driverID.jsonValue});

        if(driverIDValidCheck === undefined) {
            $(dataToCheck.driverID.parentNode).removeClass('bg-yellow-600');
            $(dataToCheck.driverID.parentNode).removeClass('bg-blue-600');
            $(dataToCheck.driverID.parentNode).addClass('bg-red-600');

            $(dataToCheck.driverID.warningNode).slideUp(500);
            $(dataToCheck.driverID.alertNode).slideDown(500);
        }

        $(dataToCheck.driverID.selectInpNode).change(function(event) {
            let selectedValue = $(dataToCheck.driverID.selectInpNode).val();

            json.results[i].driver_id = parseInt(selectedValue);
            jsonResultsDetailsStore.driverID[i] = parseInt(selectedValue);

            supportingVariables.availableDrivers = dataToCheck.driverID.allValues.filter(ele => !jsonResultsDetailsStore.driverID.includes(ele.id));

            dataToCheck.driverID.alertNode = `#errorDriverAlert${supportingVariables.indexPosMap[i] - 1}`;

            repopulateDriverResultsDropdowns(json, dataToCheck.driverID.allValues, supportingVariables);
            
            checkIfDriverNameIsExactMatch(jsonResultsDetailsStore, supportingVariables);
            checkDuplicateDiD(jsonResultsDetailsStore, supportingVariables);
            clearSelectWarning(dataToCheck.driverID.selectInpNode, dataToCheck.driverID.allValues, selectedValue, dataToCheck.driverID.parentNode, dataToCheck.driverID.alertNode);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.driverID.undoBtn, dataToCheck.driverID.selectInpNode, dataToCheck.driverID.jsonValue);
    }

    function repopulateDriverResultsDropdowns(json, driver, supportingVariables) {
        for(let x = 0; x < json.results.length; x++) {
            let driverSelect = "<option hidden selected value> -- Missing ID -- </option>";
            let driverDispVal;
            let driverCol = document.getElementById(`driverSelect${x}`);

            for(let y = 0; y < driver.length; y++) {
                if(driver[y].id === json.results[x].driver_id) {
                    driverDispVal = `${driver[y].name} (${driver[y].id})`;
                    driverSelect += `<option selected value=${driver[y].id}>${driverDispVal}</option>`;
                }
            }

            for(let y = 0; y < supportingVariables.availableDrivers.length; y++) {
                driverDispVal = `${supportingVariables.availableDrivers[y].name} (${supportingVariables.availableDrivers[y].id})`;  
                driverSelect += `<option value=${supportingVariables.availableDrivers[y].id}>${driverDispVal}</option>`;
            }

            driverCol.innerHTML = driverSelect;
        }
    }

    function checkAndMonitorConstructorId(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        let constructorIDValidCheck = dataToCheck.constructorID.allValues.find(item => {return item.id === dataToCheck.constructorID.jsonValue});

        if(constructorIDValidCheck === undefined) {
            $(dataToCheck.constructorID.parentNode).addClass('bg-red-600');
            $(dataToCheck.constructorID.alertNode).slideDown(500);
        }

        $(dataToCheck.constructorID.selectInpNode).change(function(event) {
            let selectedValue = $(dataToCheck.constructorID.selectInpNode).val();
            
            dataToCheck.constructorID.alertNode = `#errorConstructorAlert${supportingVariables.indexPosMap[i] - 1}`;

            clearSelectWarning(dataToCheck.constructorID.selectInpNode, dataToCheck.constructorID.allValues, selectedValue, dataToCheck.constructorID.parentNode, dataToCheck.constructorID.alertNode);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.constructorID.undoBtn, dataToCheck.constructorID.selectInpNode, dataToCheck.constructorID.jsonValue);
    }

    function checkAndMonitorStartingGrid(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        inputFormatCheck(dataToCheck.grid.jsonValue, dataToCheck.grid.parentNode, dataToCheck.grid.inpNode, dataToCheck.grid.alertNode);

        $(dataToCheck.grid.inpNode).change(function(event) {
            let selectedGridVal = $(dataToCheck.grid.inpNode).val();

            jsonResultsDetailsStore.grid[i] = parseInt(selectedGridVal);

            dataToCheck.grid.alertNode = `#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`;
            
            inputFormatCheck(parseInt(selectedGridVal), dataToCheck.grid.parentNode, dataToCheck.grid.inpNode, dataToCheck.grid.alertNode);
            checkDuplicateGrid(jsonResultsDetailsStore, supportingVariables);
            checkGridValueGreaterThanArraySize(jsonResultsDetailsStore, supportingVariables);
            checkGridValuesStartWith1(jsonResultsDetailsStore, supportingVariables);
            checkGridValuesForBreakInSequence(jsonResultsDetailsStore, supportingVariables);
            isAllGridValues0(jsonResultsDetailsStore, supportingVariables);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.grid.undoBtn, dataToCheck.grid.inpNode, dataToCheck.grid.jsonValue);
    }

    function checkDuplicateGrid(jsonResultsDetailsStore, supportingVariables) {
        let duplicateGrid = findDuplicateIds(jsonResultsDetailsStore.grid);

        for(let i = 0; i < jsonResultsDetailsStore.grid.length; i++) {
            if(
                !isNaN(jsonResultsDetailsStore.grid[i]) && 
                jsonResultsDetailsStore.grid[i] != null && 
                jsonResultsDetailsStore.grid[i] != 0
            ) {
                let j = 0;
                for(j; j < duplicateGrid.length; j++) {
                    if(duplicateGrid[j] === jsonResultsDetailsStore.grid[i]) {
                        break;
                    }
                }
                
                if(j === duplicateGrid.length) {
                    $(`#resultsBodyGrid${i}`).removeClass('bg-red-600');
                    $(`#inputGrid${i}`).removeClass('bg-red-600');
                    $(`#inputGrid${i}`).removeClass('font-bold');
                    $(`#inputGrid${i}`).removeClass('text-white');
                    $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STARTING GRID</strong> [field must be a positive integer]</p>`
                    );
                    $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
                }
                else {
                    $(`#resultsBodyGrid${i}`).addClass('bg-red-600');
                    $(`#inputGrid${i}`).addClass('bg-red-600');
                    $(`#inputGrid${i}`).addClass('font-bold');
                    $(`#inputGrid${i}`).addClass('text-white');
                    $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                        `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STARTING GRID</strong> [field is a duplicate starting grid value]</p>`
                    );
                    $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
                }
            }
            else {
                $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).html(`<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> STARTING GRID</strong> [field must be a positive integer]</p>`);
            }
        } 
    }

    function checkGridValueGreaterThanArraySize(jsonResultsDetailsStore, supportingVariables) {
        let maxGrid = jsonResultsDetailsStore.grid[0];

        for(let i = 0; i < jsonResultsDetailsStore.grid.length; i++) {
            if(jsonResultsDetailsStore.grid[i] > maxGrid) {
                maxGrid = jsonResultsDetailsStore.grid[i];
            }
        }

        let maxGridIdx = jsonResultsDetailsStore.grid.indexOf(maxGrid);

        if(jsonResultsDetailsStore.grid[maxGridIdx] > jsonResultsDetailsStore.grid.length) {
            $(`#resultsBodyGrid${maxGridIdx}`).addClass('bg-red-600');
            $(`#inputGrid${maxGridIdx}`).addClass('bg-red-600');
            $(`#inputGrid${maxGridIdx}`).addClass('font-bold');
            $(`#inputGrid${maxGridIdx}`).addClass('text-white');
            $(`#errorGridAlert${supportingVariables.indexPosMap[maxGridIdx] - 1}`).html(
                `<p>Row<strong> ${supportingVariables.indexPosMap[maxGridIdx]}</strong> -<strong> STARTING GRID</strong> [field value is greater than the number of position entries]</p>`
            );
            $(`#errorGridAlert${supportingVariables.indexPosMap[maxGridIdx] - 1}`).slideDown(500);
        }
        else if(
            jsonResultsDetailsStore.grid.length > 1 && 
            jsonResultsDetailsStore.grid[maxGridIdx] <= jsonResultsDetailsStore.grid.length && 
            $(`#resultsBodyGrid${maxGridIdx}`).hasClass('bg-red-600')
        ) {
            $(`#resultsBodyGrid${maxGridIdx}`).removeClass('bg-red-600');
            $(`#inputGrid${maxGridIdx}`).removeClass('bg-red-600');
            $(`#inputGrid${maxGridIdx}`).removeClass('font-bold');
            $(`#inputGrid${maxGridIdx}`).removeClass('text-white');
            $(`#errorGridAlert${supportingVariables.indexPosMap[maxGridIdx] - 1}`).slideUp(500);
        }
    }

    function checkGridValuesStartWith1(jsonResultsDetailsStore, supportingVariables) {
        let minGrid = jsonResultsDetailsStore.grid[0];

        for(let i = 0; i < jsonResultsDetailsStore.grid.length; i++) {
            if(jsonResultsDetailsStore.grid[i] < minGrid) {
                minGrid = jsonResultsDetailsStore.grid[i];
            }
        }

        let minGridIdx = jsonResultsDetailsStore.grid.indexOf(minGrid);
        
        if(!jsonResultsDetailsStore.grid.includes(1) && !$(`#resultsBodyGrid${minGrid}`).hasClass('bg-red-600')) {
            $(`#resultsBodyGrid${minGridIdx}`).addClass('bg-red-600');
            $(`#inputGrid${minGridIdx}`).addClass('bg-red-600');
            $(`#inputGrid${minGridIdx}`).addClass('font-bold');
            $(`#inputGrid${minGridIdx}`).addClass('text-white');
            $(`#errorGridAlert${supportingVariables.indexPosMap[minGridIdx] - 1}`).html(
                `<p>Row<strong> ${supportingVariables.indexPosMap[minGridIdx]}</strong> -<strong> STARTING GRID</strong> [grid sequence should start with <strong>1</strong>]</p>`
            );
            $(`#errorGridAlert${supportingVariables.indexPosMap[minGridIdx] - 1}`).slideDown(500);
        }
    }

    function checkGridValuesForBreakInSequence(jsonResultsDetailsStore, supportingVariables) {
        let breakIdx, isSequenceBroken = 0, increment = 0, expectedVal = 1;
        let previousGridVal = 0;
        
        let sortedGridStore = jsonResultsDetailsStore.grid.slice().sort((a, b) => a - b);

        for(let i = 0; i < sortedGridStore.length; i++) {
            if(sortedGridStore[i] == previousGridVal) {
                increment++;
                
                if(sortedGridStore[i] != 0) {
                    expectedVal++;
                }

                continue;
            }

            if(sortedGridStore[i] != sortedGridStore[0] + increment) {
                breakIdx = jsonResultsDetailsStore.grid.indexOf(sortedGridStore[i]);
                isSequenceBroken = 1;
                
                break;
            }

            increment++;
            expectedVal++;
            previousGridVal = sortedGridStore[i];
        }

        if(isSequenceBroken && !$(`#resultsBodyGrid${breakIdx}`).hasClass('bg-red-600')) {
            $(`#resultsBodyGrid${breakIdx}`).addClass('bg-red-600');
            $(`#inputGrid${breakIdx}`).addClass('bg-red-600');
            $(`#inputGrid${breakIdx}`).addClass('font-bold');
            $(`#inputGrid${breakIdx}`).addClass('text-white');
            $(`#errorGridAlert${supportingVariables.indexPosMap[breakIdx] - 1}`).html(
                `<p>Row<strong> ${supportingVariables.indexPosMap[breakIdx]}</strong> -<strong> STARTING GRID</strong> [break in grid sequence, <strong>${expectedVal}</strong> is expected]</p>`
            );
            $(`#errorGridAlert${supportingVariables.indexPosMap[breakIdx] - 1}`).slideDown(500);
        }
    }

    function isAllGridValues0(jsonResultsDetailsStore, supportingVariables) {
        let isAllGridValuesZero = !jsonResultsDetailsStore.grid.some(item => item !== 0);

        if(isAllGridValuesZero) {
            for(let i = 0; i < jsonResultsDetailsStore.grid.length; i++) {
                $(`#resultsBodyGrid${i}`).removeClass('bg-red-600');
                $(`#inputGrid${i}`).removeClass('bg-red-600');
                $(`#inputGrid${i}`).removeClass('font-bold');
                $(`#inputGrid${i}`).removeClass('text-white');
                $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
            }
        } else {
            for(let i = 0; i < jsonResultsDetailsStore.grid.length; i++) {
                if(jsonResultsDetailsStore.grid[i] === 0) {
                    $(`#resultsBodyGrid${i}`).addClass('bg-red-600');
                    $(`#inputGrid${i}`).addClass('bg-red-600');
                    $(`#inputGrid${i}`).addClass('font-bold');
                    $(`#inputGrid${i}`).addClass('text-white');
                    $(`#errorGridAlert${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
                }
            }
        }
    }

    function checkAndMonitorLapsCompleted(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        inputFormatCheck(dataToCheck.stops.jsonValue, dataToCheck.stops.parentNode, dataToCheck.stops.inpNode, dataToCheck.stops.alertNode, 0);

        $(dataToCheck.stops.inpNode).change(function(event) {
            let selectedStopsVal = $(dataToCheck.stops.inpNode).val();
            let nextRow = $(this).closest('tr').next();
            
            jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 1] = parseInt(selectedStopsVal);
            
            dataToCheck.stops.alertNode = `#errorStopsAlert${supportingVariables.indexPosMap[i] - 1}`;

            checkIfCurrentTimeLessThanPosAbove(json, jsonResultsDetailsStore, supportingVariables, i);
            
            if(nextRow.length) {
                let nextIndex = parseInt((nextRow[0].querySelector('.inputPos').id).match(/\d+/g)[0]);
                
                checkIfCurrentTimeLessThanPosAbove(json, jsonResultsDetailsStore, supportingVariables, nextIndex);
            }
            
            inputFormatCheck(parseInt(selectedStopsVal), dataToCheck.stops.parentNode, dataToCheck.stops.inpNode, dataToCheck.stops.alertNode, 0);
            
            if((supportingVariables.indexPosMap[i] - 1) === 0) {
                for(let x = 0; x < json.results.length; x++) {
                    checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, x);
                }  
            }
            else {
                checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, i);
            }
            
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.stops.undoBtn, dataToCheck.stops.inpNode, dataToCheck.stops.jsonValue);
    }

    function checkIfCurrentTimeLessThanPosAbove(json, jsonResultsDetailsStore, supportingVariables, i) {
        // Skipping check for first row
        if(supportingVariables.indexPosMap[i] === 1) {
            if($(`#resultsBodyStops${i}`).hasClass('bg-yellow-600')) {
                $(`#resultsBodyStops${i}`).removeClass('bg-yellow-600');
                $(`#inputStops${i}`).removeClass('bg-yellow-600');
            }

            return;
        }

        let prevRaceTime = jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 2];
        let currentRaceTime = jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 1];

        let prevRaceTimeInSeconds = convertTimeFormatToSeconds(prevRaceTime);
        let currentRaceTimeInSeconds = convertTimeFormatToSeconds(currentRaceTime);
        
        let prevStops = jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 2];
        let currentStops = jsonResultsDetailsStore.stops[supportingVariables.indexPosMap[i] - 1];

        if(prevStops < currentStops) {
            currentLapsCompletedIsGreaterThanRowAbove(supportingVariables, i);
        }
        else if(
            !supportingVariables.isTimeNotNumber.test(currentRaceTime) && 
            !supportingVariables.isTimeNotNumber.test(prevRaceTime)
        ) {
            if((currentRaceTimeInSeconds < prevRaceTimeInSeconds) && (currentStops === prevStops)) {
                currentRaceTimeIsLessThanRowAboveWithSameLapsCompleted(supportingVariables, currentStops, i);
            }
            else {
                removeAllTimeAndLapsRelatedWarnings(supportingVariables, i);

                if(!$(`#inputTime${i}`).hasClass('bg-red-600')) {
                    $(`#inputTime${i}`).removeClass('font-bold');
                    $(`#inputTime${i}`).removeClass('text-white');
                }
            }
        }
        else {
            removeAllTimeAndLapsRelatedWarnings(supportingVariables, i);

            $(`#inputTime${i}`).removeClass('font-bold');
            $(`#inputTime${i}`).removeClass('text-white');
        }
    }

    function currentLapsCompletedIsGreaterThanRowAbove(supportingVariables, i) {
        $(`#resultsBodyStops${i}`).addClass('bg-yellow-600');
        $(`#resultsBodyTime${i}`).removeClass('bg-yellow-600');
        $(`#inputStops${i}`).addClass('bg-yellow-600');
        $(`#inputTime${i}`).removeClass('bg-yellow-600');
        $(`#inputStops${i}`).addClass('font-bold');
        $(`#inputStops${i}`).addClass('text-white');
        $(`#inputTime${i}`).removeClass('font-bold');
        $(`#inputTime${i}`).removeClass('text-white');

        $(`#warningRaceTimeFasterThanPrevPos${supportingVariables.indexPosMap[i] - 1}`).html(
            `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> LAPS COMPLETED</strong> is more than the that of row above</p>`
        );
        $(`#warningRaceTimeFasterThanPrevPos${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
    }

    function currentRaceTimeIsLessThanRowAboveWithSameLapsCompleted(supportingVariables, currentStops, i) {
        $(`#resultsBodyStops${i}`).removeClass('bg-yellow-600');
        $(`#inputStops${i}`).removeClass('bg-yellow-600');
        $(`#inputTime${i}`).addClass('font-bold');
        $(`#inputTime${i}`).addClass('text-white');
        
        if(!$(`#resultsBodyTime${i}`).hasClass('bg-red-600')) {
            $(`#resultsBodyTime${i}`).addClass('bg-yellow-600');
            $(`#inputTime${i}`).addClass('bg-yellow-600');
        }

        if(!$(`#inputStops${i}`).hasClass('bg-red-600')) {
            $(`#inputStops${i}`).removeClass('font-bold');
            $(`#inputStops${i}`).removeClass('text-white');
        }

        $(`#warningRaceTimeFasterThanPrevPos${supportingVariables.indexPosMap[i] - 1}`).html(
            `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> RACE TIME</strong> is less than the that of row above having <strong>COMPLETED ${currentStops} LAPS</stron></p>`
        );
        $(`#warningRaceTimeFasterThanPrevPos${supportingVariables.indexPosMap[i] - 1}`).slideDown(500);
    }

    function removeAllTimeAndLapsRelatedWarnings(supportingVariables, i) {
        $(`#resultsBodyTime${i}`).removeClass('bg-yellow-600');
        $(`#resultsBodyStops${i}`).removeClass('bg-yellow-600');
        $(`#inputTime${i}`).removeClass('bg-yellow-600');
        $(`#inputStops${i}`).removeClass('bg-yellow-600');

        if(!$(`#inputStops${i}`).hasClass('bg-red-600')) {
            $(`#inputStops${i}`).removeClass('font-bold');
            $(`#inputStops${i}`).removeClass('text-white');
        }

        $(`#warningRaceTimeFasterThanPrevPos${supportingVariables.indexPosMap[i] - 1}`).slideUp(500);
    }

    function checkAndMonitorFastestLap(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        timeCheck(regexValidationStrings.regexFastestLap, dataToCheck.fastestLap.jsonValue, dataToCheck.fastestLap.inpNode, dataToCheck.fastestLap.parentNode, dataToCheck.fastestLap.alertNode);

        $(dataToCheck.fastestLap.inpNode).change(function(event) {
            let selectedFastestLapVal = $(dataToCheck.fastestLap.inpNode).val();
            
            json.results[i].fastestlaptime = selectedFastestLapVal;

            dataToCheck.fastestLap.alertNode = `#errorFlAlert${supportingVariables.indexPosMap[i] - 1}`;

            timeCheck(regexValidationStrings.regexFastestLap, selectedFastestLapVal, dataToCheck.fastestLap.inpNode, dataToCheck.fastestLap.parentNode, dataToCheck.fastestLap.alertNode);
            isFastestLapPresentAndMatchingWithStatus(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        resetField(dataToCheck.fastestLap.undoBtn, dataToCheck.fastestLap.inpNode, dataToCheck.fastestLap.jsonValue);
    }

    function timeCheck(regex, value, inpNode, parentNode, alertNode) {
        let timeCheck = new RegExp(regex);

        if(!timeCheck.test(value)) {
            if($(parentNode).hasClass('bg-yellow-600')) {
                $(parentNode).removeClass('bg-yellow-600');
                $(inpNode).removeClass('bg-yellow-600');
            }

            $(parentNode).addClass('bg-red-600');
            $(inpNode).addClass('bg-red-600');
            $(inpNode).addClass('font-bold');
            $(inpNode).addClass('text-white');
            $(alertNode).slideDown(500);   
        } else {
            $(parentNode).removeClass('bg-red-600');
            $(inpNode).removeClass('bg-red-600');
            $(inpNode).removeClass('font-bold');

            if(!$(parentNode).hasClass('bg-yellow-600')) {
                $(inpNode).removeClass('text-white');
            }

            $(alertNode).slideUp(500);    
        }
    }

    function isFastestLapPresentAndMatchingWithStatus(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables) {
        let flFormatCheck = new RegExp(regexValidationStrings.regexFastestLap);
        let shortestTime = Number.MAX_VALUE;
        let isFastestLapPresent = jsonResultsDetailsStore.status.includes(1);
        let fastestLapRowPosition, isAtleastOneFlTImeValid = 0;
        
        for(let i = 0; i < json.results.length; i++) {
            let currentFl = json.results[i].fastestlaptime;

            if(flFormatCheck.test(currentFl)) {
                let currentFastestLapInSeconds = convertTimeFormatToSeconds(json.results[i].fastestlaptime);

                if(currentFastestLapInSeconds <= shortestTime) {
                    shortestTime = currentFastestLapInSeconds;
                    fastestLapRowPosition = supportingVariables.indexPosMap[i];
                    supportingVariables.fastestLapIndex.current = i;
                }

                isAtleastOneFlTImeValid = 1;
            }
        }

        if(
            isAtleastOneFlTImeValid && 
            jsonResultsDetailsStore.status[supportingVariables.fastestLapIndex.current] === 0
        ) {
            $('#warningFlStatusNotMatchingAlert').html(
                `Row <strong>${fastestLapRowPosition}</strong> with fastest lap time does not have 'Fastest Lap' <strong>STATUS</strong>`
            );
            $(`#resultsBodyStatus${supportingVariables.fastestLapIndex.previous}`).removeClass('bg-yellow-600');
            $(`#resultsBodyStatus${supportingVariables.fastestLapIndex.current}`).addClass('bg-yellow-600');
            $('#warningFlStatusNotMatchingAlert').slideDown(500);
        }
        else {
            $(`#resultsBodyStatus${supportingVariables.fastestLapIndex.previous}`).removeClass('bg-yellow-600');
            $(`#resultsBodyStatus${supportingVariables.fastestLapIndex.current}`).removeClass('bg-yellow-600');
            $('#warningFlStatusNotMatchingAlert').slideUp(500);
        }

        if(!isFastestLapPresent) {
            $('#warningNoPosWithStatus1Alert').slideDown(500);
        }
        else {
            $('#warningNoPosWithStatus1Alert').slideUp(500);
        }

        supportingVariables.fastestLapIndex.previous = supportingVariables.fastestLapIndex.current;
    }

    function checkAndMonitorRaceTime(json, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, supportingVariables, i) {
        let originalAbsoluteRaceTime = jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 1];
        let originalIntervalRaceTime = jsonResultsDetailsStore.raceTimeInIntervals[supportingVariables.indexPosMap[i] - 1];

        checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, i);

        checkIfCurrentTimeLessThanPosAbove(json, jsonResultsDetailsStore, supportingVariables, i);

        checkTimeFormatForAllPositions(dataToCheck, dataToCheck.time.jsonValue, regexValidationStrings, supportingVariables, i);

        disableToggleBtnsOnTimeError(json, regexValidationStrings, supportingVariables);

        $(dataToCheck.time.inpNode).change(function(event) {
            let selectedTimeVal = $(dataToCheck.time.inpNode).val();
            
            if($('.raceTimeCol').hasClass('absoluteTime')) {
                jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 1] = selectedTimeVal;
                
                if((supportingVariables.indexPosMap[i] - 1) === 0) {
                    changeInRaceTimeOfPosition1(json, selectedTimeVal, jsonResultsDetailsStore, jsonResultsDetailsStore.raceTimeInAbsolutes, supportingVariables, i);
                }
                else {
                    jsonResultsDetailsStore.raceTimeInIntervals[supportingVariables.indexPosMap[i] - 1] = selectedTimeVal;
                    json.results[supportingVariables.indexPosMap[i] - 1].time = selectedTimeVal;

                    if(!supportingVariables.isTimeNotNumber.test(selectedTimeVal)) {
                        jsonResultsDetailsStore.raceTimeInIntervals[supportingVariables.indexPosMap[i] - 1] = convertAbsoluteTimeToInterval(selectedTimeVal, jsonResultsDetailsStore.raceTimeInIntervals[0]);
                    }
                }
            }
            else {
                jsonResultsDetailsStore.raceTimeInIntervals[supportingVariables.indexPosMap[i] - 1] = selectedTimeVal;

                if((supportingVariables.indexPosMap[i] - 1) === 0) {
                    changeInRaceTimeOfPosition1(json, selectedTimeVal, jsonResultsDetailsStore, jsonResultsDetailsStore.raceTimeInIntervals, supportingVariables, i);
                }
                else {
                    jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 1] = selectedTimeVal;
                    json.results[supportingVariables.indexPosMap[i] - 1].time = selectedTimeVal;
                    
                    if(!supportingVariables.isTimeNotNumber.test(selectedTimeVal)) {
                        jsonResultsDetailsStore.raceTimeInAbsolutes[supportingVariables.indexPosMap[i] - 1] = convertIntervalTimeToAbsolute(selectedTimeVal, jsonResultsDetailsStore.raceTimeInIntervals[0]);
                    }
                }
            }

            checkIfCurrentTimeLessThanPosAbove(json, jsonResultsDetailsStore, supportingVariables, i);

            dataToCheck.time.alertNode = `#errorTimeAlert${supportingVariables.indexPosMap[i] - 1}`;

            checkTimeFormatForAllPositions(dataToCheck, selectedTimeVal, regexValidationStrings, supportingVariables, i);

            disableToggleBtnsOnTimeError(json, regexValidationStrings, supportingVariables);
            checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, i);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        });

        $(dataToCheck.time.undoBtn).click(function(event) {
            if($('.raceTimeCol').hasClass('absoluteTime')) {
                $(dataToCheck.time.inpNode).val(originalAbsoluteRaceTime);
            }
            else {
                let oldIntervalRaceTime = convertAbsoluteTimeToInterval(originalAbsoluteRaceTime, jsonResultsDetailsStore.raceTimeInIntervals[0]);

                if((supportingVariables.indexPosMap[i] - 1) === 0 || originalIntervalRaceTime === '') {
                    oldIntervalRaceTime = originalAbsoluteRaceTime;
                }
                
                if(supportingVariables.isTimeNotNumber.test($(`#inputTime${i}`).val())) {
                    if(!supportingVariables.isTimeNotNumber.test(originalIntervalRaceTime)) {
                        originalIntervalRaceTime = convertAbsoluteTimeToInterval(originalAbsoluteRaceTime, jsonResultsDetailsStore.raceTimeInIntervals[0]);
                    }
                    oldIntervalRaceTime = originalIntervalRaceTime;
                }

                $(dataToCheck.time.inpNode).val(oldIntervalRaceTime);
            }

            const e = new Event("change");
            const element = document.querySelector(dataToCheck.time.inpNode);
            element.dispatchEvent(e);
        });
    }

    function checkTimeFormatForAllPositions(dataToCheck, value, regexValidationStrings, supportingVariables, i) {
        if((supportingVariables.indexPosMap[i] - 1) === 0) {
            $(`#errorTimeAlert${supportingVariables.indexPosMap[i] - 1}`).html(
                `<p>Row<strong> ${supportingVariables.indexPosMap[i]}</strong> -<strong> RACE TIME</strong> [field must be in one of the following formats:<strong> '-'</strong> or <strong>'1:06.006'</strong>`
            );

            timeCheck(regexValidationStrings.regexTimeIsAbsolute, value, dataToCheck.time.inpNode, dataToCheck.time.parentNode, dataToCheck.time.alertNode);
        }
        else {
            if($('.raceTimeCol').hasClass('absoluteTime')) {
                timeCheck(regexValidationStrings.regexTimeIsAbsolute, value, dataToCheck.time.inpNode, dataToCheck.time.parentNode, dataToCheck.time.alertNode);
            }
            else {
                timeCheck(regexValidationStrings.regexTimeIsInterval, value, dataToCheck.time.inpNode, dataToCheck.time.parentNode, dataToCheck.time.alertNode);
            }
        }
    }

    function disableToggleBtnsOnTimeError(json, regexValidationStrings, supportingVariables) {
        let noTimeErrors = 1;
        let raceTimePos1 = json.results[0].time;

        for(let i = 0; i < json.results.length; i++) {
            noTimeErrors -= checkForError(`#resultsBodyTime${i}`);
        }

        if(noTimeErrors === 1) {
            if(
                supportingVariables.isTimeInPosition1Absolute.test(raceTimePos1) && 
                !$('#rowReorderToggleBtn').hasClass('editing')
            ) {
                $(("#raceTimeFormatToggleBtn")).removeClass('disableActionBtns text-white');
                $("#raceTimeFormatBtnWrapper").removeClass('tooltip');
                $(("#raceTimeFormatToggleBtn")).addClass('text-red-700');
            }
            
            if(!$('#rowReorderToggleBtn').hasClass('editing')) {
                $(("#rowReorderToggleBtn")).removeClass('disableActionBtns text-white');
                $("#rowReorderBtnWrapper").removeClass('tooltip');
                $(("#rowReorderToggleBtn")).addClass('text-orange-700');
            }
        }
        else {
            $(("#raceTimeFormatToggleBtn")).addClass('disableActionBtns text-white');
            $("#raceTimeFormatBtnWrapper").addClass('tooltip');
            $(("#raceTimeFormatToggleBtn")).removeClass('text-red-700');

            $(("#rowReorderToggleBtn")).addClass('disableActionBtns text-white');
            $("#rowReorderBtnWrapper").addClass('tooltip');
            $(("#rowReorderToggleBtn")).removeClass('text-orange-700');
        }


        if(!supportingVariables.isTimeInPosition1Absolute.test(raceTimePos1)) {
            $(("#raceTimeFormatToggleBtn")).addClass('disableActionBtns text-white');
            $("#raceTimeFormatBtnWrapper").addClass('tooltip');
            $(("#raceTimeFormatToggleBtn")).removeClass('text-red-700');

            if(!$('.raceTimeCol').hasClass('absoluteTime')) {
                const e = new Event("click");
                const element = document.querySelector("#raceTimeFormatToggleBtn");
                element.dispatchEvent(e);
            }
        }
        else {
            if(noTimeErrors === 1 && (!$('#rowReorderToggleBtn').hasClass('editing'))) {
                $(("#raceTimeFormatToggleBtn")).removeClass('disableActionBtns text-white');
                $("#raceTimeFormatBtnWrapper").removeClass('tooltip');
                $(("#raceTimeFormatToggleBtn")).addClass('text-red-700');
            }
        }
    }

    function changeInRaceTimeOfPosition1(json, selectedTimeVal, jsonResultsDetailsStore, timeFormat, supportingVariables, i) {
        jsonResultsDetailsStore.raceTimeInAbsolutes[0] = selectedTimeVal;
        jsonResultsDetailsStore.raceTimeInIntervals[0] = selectedTimeVal;
        json.results[0].time = selectedTimeVal;

        if(supportingVariables.isTimeInPosition1Absolute.test(timeFormat[0])) {
            $(`#resultsBodyTime${i}`).removeClass('bg-yellow-600');
            $(`#inputTime${i}`).removeClass('bg-yellow-600');
            $(`#inputTime${i}`).removeClass('font-bold');
            $(`#inputTime${i}`).removeClass('text-white');
            $('#warningRaceTimeFirstPosNotAbsoluteAlert').slideUp(500);
        }
        else {
            $(`#resultsBodyTime${i}`).addClass('bg-yellow-600');
            $(`#inputTime${i}`).addClass('bg-yellow-600');
            $(`#inputTime${i}`).addClass('font-bold');
            $(`#inputTime${i}`).addClass('text-white');
            $('#warningRaceTimeFirstPosNotAbsoluteAlert').slideDown(500);
        }

        if(supportingVariables.isTimeInPosition1Absolute.test(selectedTimeVal)) {
            for(let x = 1; x < json.results.length; x++) {
                if(!supportingVariables.isTimeNotNumber.test(jsonResultsDetailsStore.raceTimeInIntervals[x])) {
                    jsonResultsDetailsStore.raceTimeInIntervals[x] = convertAbsoluteTimeToInterval(jsonResultsDetailsStore.raceTimeInAbsolutes[x], jsonResultsDetailsStore.raceTimeInIntervals[0]);

                    if(!$('.raceTimeCol').hasClass('absoluteTime')) {
                        $(`#inputTime${supportingVariables.indexPosMap[x] - 1}`).val(jsonResultsDetailsStore.raceTimeInIntervals[x]);
                    }
                }
            }
        }
    }

    function convertIntervalTimeToAbsolute(interval, firstPosTime) {
        let firstPosTimeInSeconds = convertTimeFormatToSeconds(firstPosTime);
        let timeToAdd = '';

        if(interval.includes('+')) {
            timeToAdd = interval.split('+')[1];
        }

        if(interval.includes('-')) {
            timeToAdd = interval.split('-')[1];
        }

        let secondsToAdd = +(convertTimeFormatToSeconds(timeToAdd));
        let absoluteTime = (firstPosTimeInSeconds + secondsToAdd).toFixed(3);

        if(interval.includes('-')) {
            absoluteTime = (firstPosTimeInSeconds - secondsToAdd).toFixed(3);
        }

        if(+absoluteTime > 60) {
            let minutes = Math.floor(absoluteTime / 60);
            let seconds = (absoluteTime % 60).toFixed(3);

            if(parseFloat(+seconds) < 10) {
                seconds = '0' + seconds;
            }
    
            absoluteTime = [minutes, seconds].join(':');
        }

        return absoluteTime;
    }

    function checkAndMonitorStatus(json, points, dataToCheck, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i) {
        let statusValidCheck = dataToCheck.status.allValues.find(item => {return item.id === dataToCheck.status.jsonValue});
        
        if(statusValidCheck === undefined) {
            $(dataToCheck.status.parentNode).addClass('bg-red-600');
            $(dataToCheck.status.alertNode).slideDown(500);
        }

        $(dataToCheck.status.selectInpNode).change(function(event) {
            let selectedValue = $(dataToCheck.status.selectInpNode).val();

            jsonResultsDetailsStore.status[i] = parseInt(selectedValue);

            dataToCheck.status.alertNode = `#errorStatusAlert${supportingVariables.indexPosMap[i] - 1}`;

            reflectFastestLap(parseInt(selectedValue), points, additionalDetailsStore, supportingVariables, i);
            clearSelectWarning(dataToCheck.status.selectInpNode, dataToCheck.status.allValues, selectedValue, dataToCheck.status.parentNode, dataToCheck.status.alertNode);
            checkDuplicateStatus(jsonResultsDetailsStore, supportingVariables);
            checkRaceTimeMatchesWithStatus(json, jsonResultsDetailsStore, supportingVariables, i);
            isFastestLapPresentAndMatchingWithStatus(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
            checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
        })

        resetField(dataToCheck.status.undoBtn, dataToCheck.status.selectInpNode, dataToCheck.status.jsonValue);
    }

    function serialiseRowReorderControls(jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i) {
        $(`#moveRowUp${i}`).click(function(event) {    
            let thisRow = $(this).closest('tr');
            let prevRow = thisRow.prev();

            swapPreviousAndCurrentRow(thisRow, prevRow, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
        })
        
        $(`#moveRowDown${i}`).click(function(event) {            
            let thisRow = $(this).closest('tr');
            let nextRow = thisRow.next();
            let isRowMovingUp = 0;
            
            swapPreviousAndCurrentRow(thisRow, nextRow, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i, isRowMovingUp);
        })

        disableFirstAndLastReorderBtns();
    }

    function swapPreviousAndCurrentRow(currentRow, otherRow, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i, isRowMovingUp = 1) {
        if(otherRow.length) {
            let currentPos = parseInt(currentRow[0].querySelector('.inputPos').value);
            let otherPos = parseInt(otherRow[0].querySelector('.inputPos').value);
            let otherIndex = parseInt((otherRow[0].querySelector('.inputPos').id).match(/\d+/g)[0]);
            let tempStops, tempStatus, tempAddPoints;

            // Swapping 'indexPosMap' values between rows
            supportingVariables.indexPosMap[i] = otherPos;
            supportingVariables.indexPosMap[otherIndex] = currentPos;

            swapDetailsStoreValues(currentPos, otherPos, jsonResultsDetailsStore.driverName);
            swapDetailsStoreValues(currentPos, otherPos, jsonResultsDetailsStore.raceTimeInAbsolutes);
            swapDetailsStoreValues(currentPos, otherPos, jsonResultsDetailsStore.originalStatusMinusUnitsPlace);
            swapDetailsStoreValues(currentPos, otherPos, jsonResultsDetailsStore.aliasExactMatchKey);
            swapDetailsStoreValues(currentPos, otherPos, additionalDetailsStore.uploadedDriverID);
            swapDetailsStoreValues(currentPos, otherPos, additionalDetailsStore.resultsPoints);

            if(isRowMovingUp) {
                currentPos -= 1;
                otherPos += 1;
    
                currentRow[0].querySelector('.inputPos').value = currentPos;
                otherRow[0].querySelector('.inputPos').value = otherPos;
                
                otherRow.before(currentRow);
            }
            else {
                currentPos += 1;
                otherPos -= 1;
                
                currentRow[0].querySelector('.inputPos').value = currentPos;
                otherRow[0].querySelector('.inputPos').value = otherPos;  
                
                otherRow.after(currentRow);
            }

            fireChangeEvents(otherIndex);
            fireChangeEvents(i);
        }

        disableFirstAndLastReorderBtns();
    }

    function swapDetailsStoreValues(currentPos, otherPos, store) {
        let tempVal = store[currentPos - 1];
        store[currentPos - 1] = store[otherPos - 1];
        store[otherPos - 1] = tempVal;
    }

    function fireChangeEvents(index) {
        let elements = [`#inputPos${index}`, `#driverSelect${index}`, `#constructorSelect${index}`, `#inputGrid${index}`, `#inputStops${index}`, `#inputFl${index}`, `#inputTime${index}`, `#statusSelect${index}`];

        const e = new Event("change");
        
        for(let i = 0; i < elements.length; i++) {
            const element = document.querySelector(elements[i]);
            element.dispatchEvent(e);
        }
    }

    function disableFirstAndLastReorderBtns() {
        let resultsTable = document.getElementById('resultsDetailsTable');
        let noOfRows = resultsTable.children[1].children.length;

        let firstUpBtn = resultsTable.children[1].children[0].children[0].children[0].children[0];
        let lastDownBtn = resultsTable.children[1].children[noOfRows - 1].children[0].children[0].children[1];

        if(noOfRows > 1) {
            let secondUpBtn = resultsTable.children[1].children[1].children[0].children[0].children[0];
            let secondLastDownBtn = resultsTable.children[1].children[noOfRows - 2].children[0].children[0].children[1];

            $(secondUpBtn).addClass('hover:bg-green-700');
            $(secondUpBtn).removeClass('opacity-50 cursor-not-allowed');

            $(secondLastDownBtn).addClass('hover:bg-red-700');
            $(secondLastDownBtn).removeClass('opacity-50 cursor-not-allowed');
        }

        $(firstUpBtn).removeClass('hover:bg-green-700');
        $(firstUpBtn).addClass('opacity-50 cursor-not-allowed');

        $(lastDownBtn).removeClass('hover:bg-red-700');
        $(lastDownBtn).addClass('opacity-50 cursor-not-allowed');
    }

    function addEventListenerToAllToggleBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        if(!supportingVariables.isUploadPartialResultChecked) {
            addEventListenerToToggleControlsBtn();
            addEventListenerToUndoToggleBtn();
            addEventListenerToRowReorderBtn(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        }

        addEventListenerToRaceTimeFormatBtn(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
    }

    function addEventListenerToToggleControlsBtn() {
        $('#toggleControlsBtn').click(function(event) {
            if($('#toggleControlsBtn').hasClass('editing')) {
                $('#toggleControlsBtn').removeClass('bg-green-500 hover:bg-green-700 border-green-700');
                $('#toggleControlsBtn').addClass('bg-blue-500 hover:bg-blue-700 border-blue-700');
                $('#toggleControlsBtn').removeClass('editing');
            }
            else {
                $('#toggleControlsBtn').removeClass('bg-blue-500 hover:bg-blue-700 border-blue-700');
                $('#toggleControlsBtn').addClass('bg-green-500 hover:bg-green-700 border-green-700');
                $('#toggleControlsBtn').addClass('editing');
            }

            $('#undoToggleBtn').toggleClass('hidden');
            $('#rowReorderToggleBtn').toggleClass('hidden');

            $('.undo').addClass('hidden');

            if($('#undoToggleBtn').hasClass('editing')) {
                $('#undoToggleBtn').removeClass('bg-blue-500 text-white hover:bg-blue-700');
                $('#undoToggleBtn').addClass('bg-white text-blue-700 hover:text-white hover:bg-blue-500');
                $('#undoToggleBtn').html('Show Reset');
                $('#undoToggleBtn').removeClass('editing');
            }

            $('.rowReorderBtn').addClass('hidden');

            if($('#rowReorderToggleBtn').hasClass('editing')) {
                $('#rowReorderToggleBtn').removeClass('bg-orange-500 text-white hover:bg-orange-700');
                $('#rowReorderToggleBtn').addClass('bg-white text-orange-700 hover:text-white hover:bg-orange-500');
                $('#rowReorderToggleBtn').html('Show Row Reorder');
                $('#rowReorderToggleBtn').removeClass('editing');
            }
            
            $('#addRemoveRowControls').toggleClass('hidden');

            if($('.selectInp').hasClass('open')) {
                $('.selectInp').attr('disabled', true);
                $('.selectInp').removeClass('open');
                $('.selectInp').addClass('cursor-not-allowed');
            }
            else {
                $('.selectInp').attr('disabled', false);
                $('.selectInp').addClass('open');
                $('.selectInp').removeClass('cursor-not-allowed');
            }
            
            $('.numInp').toggleClass('disable');
            $('.numInp').toggleClass('cursor-not-allowed');
            $('.addMoreBtn').toggleClass('disable');
            $('.addMoreBtn').toggleClass('cursor-not-allowed');
            $('#addMoreTrack').toggleClass('disable');
            $('#addMoreTrack').toggleClass('cursor-not-allowed');
        });
    }

    function addEventListenerToRaceTimeFormatBtn(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        $('#raceTimeFormatToggleBtn').click(function(event) {
            let updatedJSONFromTable = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);

            toggleTimeFormat(updatedJSONFromTable, jsonResultsDetailsStore, supportingVariables);
        });
    }

    function updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, submitFlag = 0) {
        let trackContent = tableToJSON(document.getElementById('trackDetailsTable'), jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        let resultsContent = tableToJSON(document.getElementById('resultsDetailsTable'), jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);

        // Deleting the 'undefined' created key for the extra track table column
        delete trackContent[0].undefined;

        if(submitFlag) {
            let tempNum, resultString;
            
            if(additionalDetailsStore.raceDistance != '') {
                trackContent[0].distance = additionalDetailsStore.raceDistance;
            }

            for(let i = 0; i < json.results.length; i++) {
                if(resultsContent[i].status >= 0) {
                    resultString = (jsonResultsDetailsStore.originalStatusMinusUnitsPlace[i] + resultsContent[i].status).toFixed(2);
                }
                else {
                    resultString = (-jsonResultsDetailsStore.originalStatusMinusUnitsPlace[i] + resultsContent[i].status).toFixed(2);
                }

                resultsContent[i].status = parseFloat(resultString);
                
                if(additionalDetailsStore.resultsPoints[i] !== 0) {
                    resultsContent[i].points = additionalDetailsStore.resultsPoints[i];
                }
                
                resultsContent[i].time = jsonResultsDetailsStore.raceTimeInAbsolutes[i];
                
                if(!isNaN(resultsContent[i].fastestlaptime)) {
                    resultsContent[i].fastestlaptime = resultsContent[i].fastestlaptime.toFixed(3);
                }

                if(jsonResultsDetailsStore.aliasExactMatchKey[i].isPresent) {
                    resultsContent[i].isExactMatch = jsonResultsDetailsStore.aliasExactMatchKey[i].value;
                }
            }
        }
    
        return {track: trackContent[0], results: resultsContent};
    }

    function tableToJSON(table, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        let data = [];
        let headers = [];
        let statusMap = [0, 1, -2, -3];

        let jsonKeys = {
            "Season": 'season_id',
            "Round Number": 'round',
            "Circuit": 'circuit_id',
            "Points Scheme": 'points',
            "Position": 'position',
            "Driver": 'driver',
            "Driver ID": 'driver_id',
            "Constructor": 'constructor_id',
            "Starting Grid": 'grid',
            "Laps Completed": 'stops',
            "Fastest Lap": 'fastestlaptime',
            "Race Time": 'time',
            "Status": 'status'
        }

        convertTableHeadersIntoRequiredKeyNames(headers, table, jsonKeys);

        slotCellValuesIntoAppropriateHeaders(data, headers, table, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        
        return data;
    }

    function convertTableHeadersIntoRequiredKeyNames(headers, table, jsonKeys) {
        // Looping through cells of the first row of the table
        for(let i = 0; i < table.rows[0].cells.length; i++) {
            let tableHeaderText = table.rows[0].cells[i].innerText;

            if(table.rows[0].cells[i].children[0] !== undefined) {
                tableHeaderText = table.rows[0].cells[i].children[0].innerText;
            }
            
            Object.keys(jsonKeys).forEach((key) => {
                if(tableHeaderText == key) {
                    headers[i] = jsonKeys[key];
                }
            });
        }
    }

    function slotCellValuesIntoAppropriateHeaders(data, headers, table, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        // Starting the loop from second row of the table to last row
        for(let i = 1; i < table.rows.length; i++) {
            let tableRow = table.rows[i];
            let rowContent, rowContentFormat, treeTraversal, leftCellTraversal;
            
            let rowData = {};

            // Looping through all cells of current row
            for(let j = 0; j < tableRow.cells.length; j++) {
                if (tableRow.cells[j].children[0].children.length != 0) {
                    // Traversing through the DOM tree to find the cell value
                    treeTraversal = tableRow.cells[j].children[0].children[0].options;

                    switch(headers[j]) {
                        case 'driver':
                            if(additionalDetailsStore.uploadedDriverID[i - 1] !== null && supportingVariables.isResultImported.originalVal !== 1) {
                                rowContent = jsonResultsDetailsStore.driverName[i - 1];
                            }
                            else {
                                rowContent = treeTraversal[treeTraversal.selectedIndex].innerHTML;
                                // Removing the id of driver from the string
                                rowContent = rowContent.substring(0, rowContent.lastIndexOf('(') - 1);
                            }
                            break;
                        case 'status':
                            let tempRow = treeTraversal.selectedIndex - 1;
                            rowContent = supportingVariables.usedStatusNumbers[tempRow];
                            break;
                        case 'points':
                            rowContent = tableRow.cells[j].children[0].children[0].innerHTML;
                            break;
                        case 'position':
                            rowContent = tableRow.cells[j].children[0].children[2].value;
                            break;
                        default:
                            rowContent = tableRow.cells[j].children[0].children[0].value;
                            break;
                    }
                } else if (headers[j] == 'driver_id') {
                    // Traversing through the DOM tree to find the value of the left cell
                    leftCellTraversal = tableRow.cells[1].children[0].children[0].options;

                    rowContent = parseInt(leftCellTraversal[1].value);
                } else {
                    rowContent = tableRow.cells[j].innerHTML;
                }

                rowContentFormat = Number(rowContent);
                rowData[headers[j]] = (isNaN(rowContentFormat)) ? rowContent : rowContentFormat;
            }

            data.push(rowData);
        }
    }

    function toggleTimeFormat(json, jsonResultsDetailsStore, supportingVariables) {        
        if($('.raceTimeCol').hasClass('absoluteTime')) {
            let headerText = '[Interval]';
            let btnText = 'Show Absolute Times';

            changeRaceTimeCellsToRequiredFormat(json, headerText, btnText,jsonResultsDetailsStore.raceTimeInIntervals, supportingVariables);
            
            $('.raceTimeCol').removeClass('absoluteTime');

            if($('#rowReorderToggleBtn').hasClass('editing')) {
                const e = new Event("click");
                const element = document.querySelector("#rowReorderToggleBtn");
                element.dispatchEvent(e);
            }
        }
        else {
            let headerText = '[Absolute]';
            let btnText = 'Show Intervals';

            changeRaceTimeCellsToRequiredFormat(json, headerText, btnText, jsonResultsDetailsStore.raceTimeInAbsolutes, supportingVariables);

            $('.raceTimeCol').addClass('absoluteTime');
        }
    }

    function changeRaceTimeCellsToRequiredFormat(json, headerText, btnText, store, supportingVariables) {
        $('#raceTimeFormatText').html(headerText);
            
        for(let i = 0; i < json.results.length; i++) {
            $(`#inputTime${i}`).val(store[supportingVariables.indexPosMap[i] - 1]);
            
            if((supportingVariables.indexPosMap[i] - 1) > 0) {
                changeAllRaceTimeFormatAlertMessageText(i);
            }
        }

        $('#raceTimeFormatToggleBtn').html(btnText);
    }

    function changeAllRaceTimeFormatAlertMessageText(i) {
        let alertMessage;

        if($('.raceTimeCol').hasClass('absoluteTime')) {
            alertMessage = `<p>Row<strong> ${i+1}</strong> -<strong> RACE TIME</strong> [field must be in one of the following formats:<strong> '-'</strong>, <strong>'1:06.006'</strong>, <strong>'±10.324'</strong>, <strong>'±1:06.006'</strong>, <strong>+X Lap(s)</strong>, <strong>DNS</strong>, <strong>DNF</strong> or <strong>DSQ</strong>]</p>`;
        }
        else {
            alertMessage = `<p>Row<strong> ${i+1}</strong> -<strong> RACE TIME</strong> [field must be in one of the following formats:<strong> '-'</strong>, <strong>'1:06.006'</strong>, <strong>+X Lap(s)</strong>, <strong>DNS</strong>, <strong>DNF</strong> or <strong>DSQ</strong>]</p>`
        }

        $(`#errorTimeAlert${i}`).html(alertMessage);
    }

    function addEventListenerToUndoToggleBtn() {
        $('#undoToggleBtn').click(function(event) {
            if($('#undoToggleBtn').hasClass('editing')) {
                $('#undoToggleBtn').removeClass('bg-blue-500 text-white hover:bg-blue-700');
                $('#undoToggleBtn').addClass('bg-white text-blue-700 hover:text-white hover:bg-blue-500');
                $('#undoToggleBtn').html('Show Reset');
                $('#undoToggleBtn').removeClass('editing');
            }
            else {
                $('#undoToggleBtn').removeClass('bg-white text-blue-700 hover:text-white hover:bg-blue-500');
                $('#undoToggleBtn').addClass('bg-blue-500 text-white hover:bg-blue-700');
                $('#undoToggleBtn').html('Hide Reset');
                $('#undoToggleBtn').addClass('editing');
            }

            $('.undo').toggleClass('hidden');
            $('.addMoreBtn').toggleClass('hidden');

            if($('#rowReorderToggleBtn').hasClass('editing')) {
                if(!$('#rowReorderToggleBtn').hasClass('disableActionBtns')) {
                    $('#rowReorderToggleBtn').removeClass('bg-orange-500 text-white hover:bg-orange-700');
                    $('#rowReorderToggleBtn').addClass('bg-white text-orange-700 hover:text-white hover:bg-orange-500');
                }
                $('#rowReorderToggleBtn').html('Show Row Reorder');
                $('#rowReorderToggleBtn').removeClass('editing');
                
                $('.rowReorderBtn').toggleClass('hidden');
                $('.addMoreBtn').toggleClass('hidden');
            }
        });
    }

    function addEventListenerToRowReorderBtn(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        $('#rowReorderToggleBtn').click(function(event) {
            if($('#rowReorderToggleBtn').hasClass('editing')) {
                $('#rowReorderToggleBtn').removeClass('bg-orange-500 text-white hover:bg-orange-700');
                $('#rowReorderToggleBtn').addClass('bg-white text-orange-700 hover:text-white hover:bg-orange-500');
                $('#rowReorderToggleBtn').html('Show Row Reorder');
                $('#rowReorderToggleBtn').removeClass('editing');

                disableRaceTimeFormatBtnOnRaceTimeErrors(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            }
            else {
                $('#rowReorderToggleBtn').removeClass('bg-white text-orange-700 hover:text-white hover:bg-orange-500');
                $('#rowReorderToggleBtn').addClass('bg-orange-500 text-white hover:bg-orange-700');
                $('#rowReorderToggleBtn').html('Hide Row Reorder');
                $('#rowReorderToggleBtn').addClass('editing');
                
                if(!$('.raceTimeCol').hasClass('absoluteTime')) {
                    const e = new Event("click");
                    const element = document.querySelector("#raceTimeFormatToggleBtn");
                    element.dispatchEvent(e);
                }
            }
            
            $('.rowReorderBtn').toggleClass('hidden');
            $('.addMoreBtn').toggleClass('hidden');

            if($('#undoToggleBtn').hasClass('editing')) {
                $('#undoToggleBtn').removeClass('bg-blue-500 text-white hover:bg-blue-700');
                $('#undoToggleBtn').addClass('bg-white text-blue-700 hover:text-white hover:bg-blue-500');
                $('#undoToggleBtn').html('Show Reset');
                $('#undoToggleBtn').removeClass('editing');

                $('.undo').toggleClass('hidden');
                $('.addMoreBtn').toggleClass('hidden');
            }
        });
    }

    function disableRaceTimeFormatBtnOnRaceTimeErrors(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        let updatedJSONFromTable = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
        let noTimeErrors = 1;
        
        let raceTimePos1 = $(`#inputTime${supportingVariables.indexPosMap[0] - 1}`).val();

        for(let i = 0; i < updatedJSONFromTable.results.length; i++) {
            noTimeErrors -= checkForError(`#resultsBodyTime${i}`);
        }
        
        if(noTimeErrors === 1 && supportingVariables.isTimeInPosition1Absolute.test(raceTimePos1)) {
            $("#raceTimeFormatToggleBtn").removeClass('disableActionBtns text-white');
            $("#raceTimeFormatBtnWrapper").removeClass('tooltip');
            $("#raceTimeFormatToggleBtn").addClass('text-red-700');
        }
    }

    function addEventListenerToTrackUndoBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        let allTrackUndoBtns = ['#undoSeason', '#undoRound', '#undoCircuit', '#undoPoints'];

        for(let i = 0; i < allTrackUndoBtns.length; i++) {
            $(allTrackUndoBtns[i]).click(function(event) {
                actionOnUndoBtnClick(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            });
        }
    }

    function actionOnUndoBtnClick(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        $('.undo').addClass('hidden');

        $('#undoToggleBtn').removeClass('bg-blue-500 text-white hover:bg-blue-700');
        $('#undoToggleBtn').addClass('bg-white text-blue-700 hover:text-white hover:bg-blue-500');
        $('#undoToggleBtn').html('Show Reset');
        $('#undoToggleBtn').removeClass('editing');

        $('.addMoreBtn').removeClass('hidden');

        disableRaceTimeFormatBtnOnRaceTimeErrors(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);

        if(!$('#rowReorderToggleBtn').hasClass('disableActionBtns')) {
            $('#rowReorderToggleBtn').removeClass('bg-orange-500 text-white hover:bg-orange-700');
            $('#rowReorderToggleBtn').addClass('bg-white text-orange-700 hover:text-white hover:bg-orange-500');
        }
    }

    function addEventListenerToResultsUndoBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i) {
        let allResultsUndoBtn = [`#undoDriver${i}`, `#undoConstructor${i}`, `#undoGrid${i}`, `#undoStops${i}`, `#undoFl${i}`, `#undoTime${i}`, `#undoStatus${i}`];

        for(let j = 0; j < allResultsUndoBtn.length; j++) {
            $(allResultsUndoBtn[j]).click(function(event) {
                actionOnUndoBtnClick(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            });
        }
    }

    function addEventListenerOnAddNewRowBtn(json, season, points, driver, status, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        $('#addRow').click(function(event) {
            let addRowTemplate = {
                position: json.results.length + 1,
                constructor_id: 0,
                grid: 0,
                stops: -1,
                time: "",
                fastestlaptime: "",
                status: -2,
                driver_id: null,
                driver: ""
            };
            
            pushNewRowValuesIntoStores(json, addRowTemplate, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            
            let i = json.results.length - 1;

            updateResultsTable(json, points, driver, status, additionalDetailsStore, supportingVariables, i);
            
            supportingVariables.availableDrivers = driver.filter(ele => !jsonResultsDetailsStore.driverID.includes(ele.id));

            repopulateDriverResultsDropdowns(json, driver, supportingVariables);

            addEventListenerToResultsUndoBtns(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
            
            let updatedJSONFromTable = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);
            
            if(!supportingVariables.isShowAllConstructorsChecked) {
                for(let i = 0; i < season.length; i++) {
                    if(season[i].id === updatedJSONFromTable.track.season_id) {
                        supportingVariables.availableConstructors = season[i].constructors;
                    }
                }
                repopulateConstructorResultsDropdowns(updatedJSONFromTable, supportingVariables);
            }

            if((json.results.length) >= 1) {
                $('#removeRow').removeClass('opacity-50 cursor-not-allowed');
                $('#removeRow').addClass('hover:bg-red-700');                 
            }
            
            if((updatedJSONFromTable.results.length) >= 2 && $('#toggleControlsBtn').hasClass('editing')) {
                $('.selectInp').attr('disabled', false);
                $('.selectInp').addClass('open');
                $('.selectInp').removeClass('cursor-not-allowed');
            }
            
            if($('#rowReorderToggleBtn').hasClass('editing')) {
                $(`#moveRowUp${i}`).toggleClass('hidden');
                $(`#moveRowDown${i}`).toggleClass('hidden');
            }
            
            if($('#rowReorderToggleBtn').hasClass('editing') || $('#toggleRowUndo').hasClass('editing')) {
                $(`#addMore${i}`).toggleClass('hidden');
            }
            
            checkRaceTimeMatchesWithStatus(updatedJSONFromTable, jsonResultsDetailsStore, supportingVariables, i);
            checkAndMonitorResultsData(json, points, driver, supportingVariables.availableConstructors, status, regexValidationStrings, jsonTrackDetailsStore, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);
            serialiseRowReorderControls(jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, i);

            openResultsMoreDetailsOverlay(json, additionalDetailsStore, supportingVariables, i);
            
            checkGridValueGreaterThanArraySize(jsonResultsDetailsStore, supportingVariables);
            checkGridValuesStartWith1(jsonResultsDetailsStore, supportingVariables);
            isAllGridValues0(jsonResultsDetailsStore, supportingVariables);
        });
    }

    function pushNewRowValuesIntoStores(json, addRowTemplate, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        json.results.push(addRowTemplate);
        supportingVariables.indexPosMap.push(addRowTemplate.position);
        jsonResultsDetailsStore.driverName.push(addRowTemplate.driver);
        jsonResultsDetailsStore.driverID.push(addRowTemplate.driver_id);
        jsonResultsDetailsStore.grid.push(addRowTemplate.grid);
        jsonResultsDetailsStore.stops.push(addRowTemplate.stops);
        jsonResultsDetailsStore.raceTimeInAbsolutes.push(addRowTemplate.time);
        jsonResultsDetailsStore.raceTimeInIntervals.push(addRowTemplate.time);
        jsonResultsDetailsStore.originalStatusMinusUnitsPlace.push(0);
        jsonResultsDetailsStore.status.push(addRowTemplate.status);
        jsonResultsDetailsStore.aliasExactMatchKey.push({
            isPresent: false,
            value: null
        });
        additionalDetailsStore.uploadedDriverID.push(addRowTemplate.driver_id);
        additionalDetailsStore.resultsPoints.push(0);
    }

    function addEventListenerOnRemoveLastRowBtn(json, points, driver, regexValidationStrings, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        $('#removeRow').click(function(event) {
            let errorIndex = json.results.length;
            
            if(errorIndex == 1) {
                return alert('Cannot remove row! Finishing order should have atleast one entry.');
            }

            let choice = window.confirm('Are you sure you want to delete the last row?')
            
            if(choice) {
                let updatedJSONFromTable = updateJSONFromTableValues(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables, 0);
                
                if(errorIndex > 1) {
                    popLastRowValuesFromStores(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables);

                    $('.resultRow').last().remove();
                }

                supportingVariables.availableDrivers = driver.filter(ele => !jsonResultsDetailsStore.driverID.includes(ele.id));
                
                repopulateDriverResultsDropdowns(json, driver, supportingVariables);

                slideUpAllLastRowErrorAlerts(json);

                if(errorIndex <= 2) {
                    $('#removeRow').removeClass('hover:bg-red-700');
                    $('#removeRow').addClass('opacity-50 cursor-not-allowed');
                }
                
                disableFirstAndLastReorderBtns();

                checkDuplicateGrid(jsonResultsDetailsStore, supportingVariables);
                checkGridValueGreaterThanArraySize(jsonResultsDetailsStore, supportingVariables);
                checkGridValuesStartWith1(jsonResultsDetailsStore, supportingVariables);
                checkGridValuesForBreakInSequence(jsonResultsDetailsStore, supportingVariables);
                isAllGridValues0(jsonResultsDetailsStore, supportingVariables);

                isFastestLapPresentAndMatchingWithStatus(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);
                disableToggleBtnsOnTimeError(json, regexValidationStrings, supportingVariables);
                checkAllTableValuesForErrors(json, regexValidationStrings, jsonResultsDetailsStore, supportingVariables);

                for(let i = 0; i < updatedJSONFromTable.results.length; i++) {
                    reflectFastestLap(updatedJSONFromTable.results[i].status, points, additionalDetailsStore, supportingVariables, i);
                }
            }
        });
    }

    function popLastRowValuesFromStores(json, jsonResultsDetailsStore, additionalDetailsStore, supportingVariables) {
        json.results.pop();
        supportingVariables.indexPosMap.pop();
        jsonResultsDetailsStore.driverName.pop();
        jsonResultsDetailsStore.driverID.pop();
        jsonResultsDetailsStore.grid.pop();
        jsonResultsDetailsStore.stops.pop();
        jsonResultsDetailsStore.raceTimeInAbsolutes.pop();
        jsonResultsDetailsStore.raceTimeInIntervals.pop();
        jsonResultsDetailsStore.originalStatusMinusUnitsPlace.pop();
        jsonResultsDetailsStore.status.pop();
        jsonResultsDetailsStore.aliasExactMatchKey.pop();
        additionalDetailsStore.uploadedDriverID.pop();
        additionalDetailsStore.resultsPoints.pop();
    }

    function slideUpAllLastRowErrorAlerts(json) {
        $(`#errorPosAlert${json.results.length}`).slideUp(500);
        $(`#errorDriverAlert${json.results.length}`).slideUp(500);
        $(`#errorConstructorAlert${json.results.length}`).slideUp(500);
        $(`#errorStatusAlert${json.results.length}`).slideUp(500);
        $(`#errorGridAlert${json.results.length}`).slideUp(500);
        $(`#errorStopsAlert${json.results.length}`).slideUp(500);
        $(`#errorFlAlert${json.results.length}`).slideUp(500);
        $(`#errorTimeAlert${json.results.length}`).slideUp(500);
    }

    function postJson(json, season) {
        $.ajax({
            type: "POST",
            url: "/results/race",
            data: json,
            contentType: "application/json",
            success: function (result) {
                $('#onFailure').addClass('hidden');
                $('#onSuccess').removeClass('hidden');
                $('#editScreen').toggleClass('hidden');
                $('#serverResponseScreen').toggleClass('hidden');

                $('#raceID').html(`Race result uploaded with ID <strong>${result.race.id}</strong>`);
                
                $('#download').click(function(event) {   
                    let resultJSON = {
                        track: {
                            circuit_id: result.race.circuit_id,
                            season_id: result.race.season_id,
                            round: result.race.round,
                            points: result.race.points,
                            distance: result.race.distance
                        },
                        results: []
                    };

                    for(let i = 0; i < result.result.length; i++) {
                        resultJSON.results.push({
                            position: result.result[i].position,
                            driver: JSON.parse(json).results[i].driver,
                            driver_id: result.result[i].driver_id,
                            constructor_id: result.result[i].constructor_id,
                            grid: result.result[i].grid,
                            stops: result.result[i].stops,
                            fastestlaptime: result.result[i].fastestlaptime,
                            time: result.result[i].time,
                            status: result.result[i].status,
                        })

                        if(result.result[i].points !== 0) {
                            resultJSON.results[i].points = result.result[i].points;
                        }
                    }
                    
                    let btnName = 'download';

                    downloadJSON(resultJSON, btnName, season, resultJSON.track.season_id, resultJSON.track.round, result.race.id);
                })
            },
            error: function (result, status) {
                if(result.responseJSON.error === undefined) {
                    $('#failureText').html('Someting went wrong.');
                } else {
                    $('#failureText').html(`${result.responseJSON.message} of Position <strong>${result.responseJSON.error.position}</strong>`);
                }
                $('#onSuccess').addClass('hidden');
                $('#onFailure').removeClass('hidden');
                $('#editScreen').toggleClass('hidden');
                $('#serverResponseScreen').toggleClass('hidden');
            }
        });
    }

    function downloadJSON(json, btnName, season, seasonID, roundNo, raceID = 'review') {
        let tierName;

        for(let i = 0; i < season.length; i++) {
            if(season[i].id === seasonID) tierName = season[i].tiername.split(" ").join("_");
        }

        let data = "text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(json, null, 4));
        
        $(`#${btnName}`).attr("href", "data:"+data);
        $(`#${btnName}`).attr("download", `S${seasonID}_R${roundNo}_${tierName}_${raceID}.json`);  
    }

    function disableNumberInputsAndAddMoreBtns() {
        $('.numInp').addClass('disable');
        $('.numInp').addClass('cursor-not-allowed');
        $('.addMoreBtn').addClass('disable');
        $('.addMoreBtn').addClass('cursor-not-allowed');
        $('#addMoreTrack').addClass('disable');
        $('#addMoreTrack').addClass('cursor-not-allowed');
    }

    function enableEditOnErrorAfterLoading(json, isUploadPartialResultChecked) {
        let postStatus = {
            track: 1,
            results: 1
        }

        checkEachCellValueForError(json, postStatus);

        if((postStatus.track !== 1) || (postStatus.results !== 1)) {
            $('#toggleControlsBtn').removeClass('bg-blue-500 hover:bg-blue-700 border-blue-700');
            $('#toggleControlsBtn').addClass('bg-green-500 hover:bg-green-700 border-green-700');
            $('#toggleControlsBtn').addClass('editing');

            if(!isUploadPartialResultChecked) {
                $('.selectInp').attr('disabled', false);
                $('.selectInp').addClass('open');
                $('.selectInp').removeClass('cursor-not-allowed');
                $('.numInp').removeClass('disable');
                $('.numInp').removeClass('cursor-not-allowed');
                $('.addMoreBtn').removeClass('disable');
                $('.addMoreBtn').removeClass('cursor-not-allowed');
                
                $('#undoToggleBtn').removeClass('hidden');
                $('#rowReorderToggleBtn').removeClass('hidden');
                
                $('#addRemoveRowControls').removeClass('hidden');
                
                if(json.results.length === 1) {
                    $('#removeRow').removeClass('hover:bg-red-700');
                    $('#removeRow').addClass('opacity-50 cursor-not-allowed');
                }
            }
            else {

                $('.trackSelectInp').attr('disabled', false);
                $('.trackSelectInp').addClass('open');
                $('.trackSelectInp').removeClass('cursor-not-allowed');
                $('.trackNumInp').removeClass('cursor-not-allowed');
                $('.trackNumInp').removeClass('disable');
            }
            
            $('#addMoreTrack').removeClass('disable');
            $('#addMoreTrack').removeClass('cursor-not-allowed');
        }
    }

    function fetchResultByIDAndPassForEdit(raceNumber, season, points, tracks, constructor, driver, status) {
        $.ajax({
            type: "GET",
            url: `/result/${raceNumber}`,
            contentType: "application/json",
            success: function (result) {
                let isResultImportedOrFromScratch = 1;

                result.results.sort((a,b) => a.position - b.position);

                viewJSONData(result, season, points, tracks, constructor, driver, status, isResultImportedOrFromScratch);

                disableNumberInputsAndAddMoreBtns();
                enableEditOnErrorAfterLoading(result, $('#uploadPartialResult').is(':checked'));
            },
            error: function (result, status) {
                $('#errorIncorrectRaceIDAlert').html(`<p><strong>Race ID ${raceNumber}</strong> is not present in database</p>`);
                $('#importRace').html('Import JSON');
                $('#errorIncorrectRaceIDAlert').slideDown(500);
            }
        });
    }  
</script>
@endsection