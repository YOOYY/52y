$(document).ready(function() {
    var index = 0, type = 'alipay';

    $("#infullAccount").focus();

    $("#payment .list li").click(function(){
        if($(this).data('type') === 'card'){
            alert('暂未开放!请在游戏中充值');
        }else{
            type = $(this).data('type');
            if(type === 'alipay'){
                $(".pay_title span").text('支付宝');
                // $('#selectInfullAmount li i').each(function(){
                //     $(this).text($(this).parent().data('money')*0.98);
                // })
            }else if(type === 'wechat'){
                $(".pay_title span").text('微信');
                $('#selectInfullAmount li i').each(function(){
                    $(this).text($(this).parent().data('money'));
                })
            }
        }
        reset();
    })
    // $("#payment .list li").eq(1).click();

    $('#infullAccount').blur(function(){
        accountVerify();
    })
    $('#infullAccountID').blur(function(){
        accountIDVerify();
    })

    $('#selectInfullAmount li').click(function(){
        $(this).addClass('active').siblings().removeClass('active');
        index = $(this).val();
        money = $(this).data('money');
    })

    $('#isExchange').click(function(){
        $('#exchangeLabel div').toggleClass('active');
        if($(this).attr('checked')){
            $(this).attr('checked',false);
        }else{
            $(this).attr('checked',true);
        };
    })

    //可能传入一个兑换参数
    if(+GetQueryString('ingame')){
        $('#isExchange').click();
    }

    //验证
    $('#submit').click(function(){
        alert('暂未开放!请在游戏中充值');
        return false;
        accountVerify();
        accountIDVerify();
        if(!$('#selectInfullAmount li').hasClass('active')){
            alert('充值金额不能为空!');
            return false;
        }
        //ajax
        jQuery.support.cors = true;
        var data ={
                "account": $('#infullAccount').val(),
                "accountID": $('#infullAccountID').val(),
                "index": index,
                "type": type,
                "isExchange": $('#isExchange')[0].checked,
            };
        $.ajax({
            'url':apiUrl + 'pay',
            'type': 'POST',
            'dataType': 'json',
            'data': data,
            success: function (data) {
                if(type == 'alipay'){
                    alipaySuccess(data);
                }else{
                    wechatSuccess(data);
                }
                reset();
            },
            error: function(e){
                alert('网络错误!');
                console.log(e);
                reset();
            }
        })
    })

    $(".close,.btn_cancel").click(function(){
        $('.alipay_box').hide();
        $('.wechat_box').hide();
        $('.result').hide();
        $('.mask').hide();
    })

    $(".btn_hint").click(function(){
        $('.paystate').hide();
        $('.payafter').show();
        $('.wechat_box').hide();
    })
})

function accountVerify(){
    if($('#infullAccount').val()==null||$('#infullAccount').val()==''){
        $('#accountinfo').html('<i class="fail"></i>充值账号ID不能为空');
        return false;
    }else{
        $('#accountinfo').html('<i></i>');
        return true;
    }
}
function accountIDVerify(){
    if($('#infullAccountID').val()==null||$('#infullAccountID').val()==''){
        $('#accountIDinfo').html('<i class="fail"></i>重复账号ID不能为空!');
        return false;
    }else if($('#infullAccountID').val()!=$('#infullAccount').val()){
        $('#accountIDinfo').html('<i class="fail"></i>充值账号与重复账号不一致!');
        return false;
    }else{
        $('#accountIDinfo').html('<i></i>');
        return true;
    }
}

function reset(){
    $('#infullAccount').val('');
    $('#infullAccountID').val('');
    $('#selectInfullAmount li').removeClass('active');
    $('#accountinfo').html('');
    $('#accountIDinfo').html('');
    $("#infullAccount").focus();
    index = 0;
}

BankWebInfull = function (me) {
    return me = {
            topay: function () {
            $('.alipay_box').fadeOut();
            $('.paystate').show();
        }
    }
}();

function alipaySuccess(data){
    if (data.ret == 'success') {
        $('.account').text(data.param.account);
        $('.accountID').text(data.param.accountid);
        $('.index').text(money+'元');
        $('.alipay_box').show();
        $('.mask').show();
        $('.postform').empty().append(data.data);
    } else {
        alert(data.message);
    }
}

function wechatSuccess(data){
    if (data.ret == 'success') {
        var codeimg = 'http://qrcode.578w.com/?data='+data.data.code_url;
        $('#order_account').text(data.param.account);
        $('#order_account_id').text(data.param.accountid);
        $('#order_amount').text(money);
        $('#qrcode_img').attr("src",codeimg);
        $('.wechat_box').show();
        $('.mask').show();
    } else {
        alert(data.message);
    }
}

function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return "";
}