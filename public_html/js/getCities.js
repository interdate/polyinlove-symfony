// /**
//  * Created by interdate on 12/09/2017.
//  */
//
//
//
// $(document).ready(function () {
//
//     // $('body .field.city input.city').on('focus', function(e) {
//     //     e.preventDefault();
//     //     $(this).blur();
//     // }
//
//     $('.city input').val($("#profileTwo_city option:selected").text());
//
//     $('body .field.city input.city').on('click',function (e) {
//         e.stopPropagation();
//         $(this).blur();
//         $('.dropdown').show();
//     });
//
//     $('.dropdown').on('click','div',function (e) {
//         e.stopPropagation();
//
//         $('.city input').val($(this).text());
//         $('.dropdown').toggle();
//         var id = $(this).data('id');
//         $(".select.city select").append("<option value='"+id+"' selected>"+$(this).text()+"</option>")
//
//     });
//
//     $('html').click(function(e) {
//         if(!$(e.target).hasClass('dropdown') )
//         {
//             $('.dropdown').hide();
//         }
//     });
//
//     $.ajax({
//         url: '/ajax/cities',
//         success: function (data) {
//
//             arr = JSON.parse(data);
//
//             var cities = [];
//
//             for (var i = 0; i < arr.length; i++) {
//                 cities[i] = "<div data-id='"+arr[i]['id']+"'>"+arr[i]['name']+"</div>";
//             }
//
//             $('.dropdown').append(cities);
//         }
//     });
// });
