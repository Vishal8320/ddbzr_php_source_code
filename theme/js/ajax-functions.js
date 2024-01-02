'use strict';

//  function loadmore(start){
//     if(finished)
//     return;
//     jQuery.ajax({
//         url: baseUrl+"/request/api.php?query=manage_customers",
//         data: 'start='+start,
//         type: 'post',
//         cache: false,
//         beforeSend: function() {
//                 // Show a message here
//                 $('#preloader').removeClass('d-none');
//                 setTimeout(function() {
//                 $('#preloader').fadeOut();
//                 }, 1000);
//            },
//         success: function(data){
//             if(data == 'finished'){
//                 finished = true;
//                 $('#no_more_data').toggleClass('d-none');
//             }else{
//             jQuery('#load_customers').append(data);
            
//             load_flag +=10;
            
//             }
            
      
//         }

//     });
//  }
function search_m_customer(search,print_id){
    var search = $(search).val();
    $.ajax({
        url: baseUrl+"/request/api.php?query=manage_customers&b=c_search",
        type: "POST",
        data: {search_val : search},
        dataType : "json",
        cache: false,
        success: function(data) {
            
            // $('#table-data').hide();
            // $('#search-data').removeClass('d-none');

            // $('#single_customer  tbody').after(data.row);
            // $('#single_no_more_data').addClass('d-none');
            $(print_id).html(data.row);
        }

         });
}
 function count_customers(){
    $.ajax({
        url: baseUrl+"/request/api.php?query=manage_customers&b=count_customers",
        type: "POST",
        dataType : "json",
        cache: false,
        success: function(data) {
            jQuery('#count_customers').html(data);
            // $('.table-data').css('overflow','hidden');
            // $('#no_more_data').addClass('d-none');
            
        }

         });
}
count_customers();