// JavaScript Document
$(function () {
    $(".left-menu dt").click(function () {
        var _this = $(this);
        var _dd = $(this).next("dd");
        if (_this.next().is("dd")) {
            if (_this.hasClass("current")) {
                _this.removeClass("current");
                _dd.hide();
            }
            else {
                _this.addClass("current").siblings("dt").filter(".current").next("dd").hide();
                _this.addClass("current").siblings("dt").removeClass("current");
                _this.addClass("current").next("dd").show();
            }
        }
        else {
            return false
        }
    })

    $(".nav li").click(function () {
        $(this).addClass("selected")
	        .siblings("li").removeClass("selected");

    })


    /*左侧栏目导航2*/
    $(".left-menu2 dt").click(function () {
        if ($(this).next().is("dd")) {

            if ($(this).hasClass("current")) {
                $(this).removeClass("current");
                $(this).next("dd").hide();
            }
            else {
                $(this).addClass("current").siblings("dt").filter(".current").next("dd").hide();
                $(this).addClass("current").siblings("dt").removeClass("current");
                $(this).addClass("current").next("dd").show();
            }
        }
        else {
            return false
        }

    })

    $(".left-menu2 li").hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    })


})

function selAll() {
    $("#newslist tr td input:checkbox").attr("checked", true);
}
function noSelAll() {
    $("#newslist tr td input:checkbox").attr("checked", false);
}
function blcheck(a) {
    if ($(a).attr("checked") == "checked") {
        selAll();
    }
    else {
        noSelAll();
    }
}
function mycheckbox() {
    var falg = 0;
    $("#newslist tr td input:checkbox").each(function () {
        if ($(this).attr("checked")) {
            falg = 1;
            return false;
        }
    })
    if (falg > 0)
        return true;
    else
        alert("必须选择操作项!");
    return false;
}

