function openPopover(event,popoverID){
    $('.insidePopDiv').each(function (index, element) {
        if(!$(this).hasClass('hidden') && $(this).attr('id') !== popoverID){
            $(this).addClass('hidden');
        }
    });
    let element = event.target;
    while(element.nodeName !== "A"){
        element = element.parentNode;
    }
    var popper = Popper.createPopper(element, document.getElementById(popoverID), {
        placement: 'bottom'
    });
    document.getElementById(popoverID).classList.toggle("hidden");
}
function openPopoverOut(event,popoverID){
    let element = event.target;
    while(element.nodeName !== "A"){
        element = element.parentNode;
    }
    var popper = Popper.createPopper(element, document.getElementById(popoverID), {
        placement: 'bottom'
    });
    document.getElementById(popoverID).classList.toggle("hidden");
}
$( document ).ready(function() {
    $('.pageBody').show('slow', function() {});
    $(document).on('click', '.subMenuShow', function() {
        if($(this).attr('data-origin') == 'champ'){
            $('.modalTitle').html('Championship Standings');
        }else{
            $('.modalTitle').html('Race Result');
        }
        $('.champResModal').fadeIn();
        $('#optionError').hide();
        $('#tierSelectDiv').hide();
        $('#seasonSelectDiv').hide();
        $('.lickAndSend').hide();
        $('#lickAndSend').removeAttr('data-origin');
        $('.seriesOptions').val('');
        $('.tierOptions').val('');
        $('.seasonOptions').val('');
        $('.allTierOptions').hide();
        $('.allSeasonOptions').hide();
        $('.allTierOptions').attr('disabled','disabled');
        $('.allSeasonOptions').attr('disabled','disabled');
        $('#lickAndSend').attr('data-origin', $(this).attr('data-origin'));
    });

    $(document).on('click', '#closeModal', function() {
        $('.champResModal').fadeOut();
    });

    $(document).on('change', '.seriesOptions', function() {
        $('#optionError').hide();
        $('.lickAndSend').hide();
        $('#tierSelectDiv').hide();
        $('#seasonSelectDiv').hide();
        $('.tierOptions').val('');
        $('.seasonOptions').val('');
        $('.allTierOptions').hide();
        $('.allSeasonOptions').hide();
        $('.allTierOptions').attr('disabled','disabled');
        $('.allSeasonOptions').attr('disabled','disabled');
        if($(this).val() != ''){
            $('.tiersOf_'+$(this).val()).show();
            $('.tiersOf_'+$(this).val()).removeAttr('disabled');
            $('#tierSelectDiv').show('slow', function() {});
        }
    });

    $(document).on('change', '.tierOptions', function() {
        $('#optionError').hide();
        $('.seasonOptions').val('');
        $('.allSeasonOptions').hide();
        $('.allSeasonOptions').attr('disabled','disabled');
        $('#seasonSelectDiv').hide();
        $('.lickAndSend').hide();
        if($(this).val() != ''){
            $('.tiersOf_'+$(this).val()).show();
            var series = $('.tierOptions option:selected').attr('data-series');
            var tier = $('.tierOptions option:selected').attr('data-tier');
            $('.seasonOf_'+tier+'_'+series).show();
            $('.seasonOf_'+tier+'_'+series).removeAttr('disabled');
            $('#seasonSelectDiv').show('slow', function() {});
        }
    });

    $(document).on('change', '.seasonOptions', function() {
        $('#optionError').hide();
        $('.lickAndSend').show();
    });

    $(document).on('click', '.lickAndSend', function() {
        $('#optionError').hide();
        if($('.seriesOptions').val() != '' && $('.tierOptions').val() != '' && $('.seasonOptions').val() != '' ){
            if($('#lickAndSend').attr('data-origin') == 'champ'){
                redirectLink = $('.seasonOptions option:selected').attr('data-champLink');
            }else{
                redirectLink = $('.seasonOptions option:selected').attr('data-raceLink');
            }
            window.location = redirectLink;
        }else{
            $('#optionError').show();
        }
    });

    $(document).on('click', '#content', function(e) {
        // for sidebarmenus
        let leftSidebar = $("#sidebar");
        let rightSidebar = $("#sidebarRight");
        let leftMenuBtn = $("#leftSidebarMenu");
        let rightMenuBtn = $(".rightSidebarMenu");

        if(
            !leftSidebar.is(e.target) && 
            leftSidebar.has(e.target).length === 0 &&
            !leftMenuBtn.is(e.target) && 
            leftMenuBtn.has(e.target).length === 0
        ) {
            sidebarVisible = 1;
            $('#sidebar').animate({left: '-330px', opacity: '0'});
        }

        if(
            !rightSidebar.is(e.target) && 
            rightSidebar.has(e.target).length === 0 &&
            !rightMenuBtn.is(e.target) && 
            rightMenuBtn.has(e.target).length === 0
        ) {
            sidebarRightVisible = 1
            $('#sidebarRight').animate({right: '-330px', opacity: '0'});
        }

        // for popovers
        let notThePop = $(".popOverBtn");
        let notThisPop = $(".insidePopDiv");

        if (!notThePop.is(e.target)  && notThePop.has(e.target).length === 0 && !notThisPop.is(e.target)  && notThisPop.has(e.target).length === 0 )
        {
            $('.insidePopDiv').addClass('hidden');
        }
    });
});

let sidebarVisible = 1;
function handleLeftMenuClick() {
    $('#main-menu').show();

    if (sidebarVisible == 1) {
        $('#sidebar').animate({left: '0px', opacity: '1'});
        sidebarVisible = 0;
    } else {
        $('#sidebar').animate({left: '-330px', opacity: '0'});
        sidebarVisible = 1;
    }
}

let sidebarRightVisible = 1;
function handleRightMenuClick() {
    $('#main-menu-right').show();

    if (sidebarRightVisible == 1) {
        $('#sidebarRight').animate({right: '0px', opacity: '1'});
        sidebarRightVisible = 0;
    } else {
        $('#sidebarRight').animate({right: '-330px', opacity: '0'});
        sidebarRightVisible = 1;
    }
}