/**
 * Created by zcl on 15/5/27.
 */
$(function() {
    $('select[name=game_id]').change(function () {
        var game_id=$("select[name=game_id] option:selected").val();
        $('#order-list').submit();
    })

    $('select[name=serv_id]').change(function () {
        var serv_id=$("select[name=serv_id] option:selected").val();
        $('#order-list').submit();
    })

    $('select[name=status]').change(function () {
        var status=$("select[name=status] option:selected").val();
        $('#order-list').submit();
    })
});