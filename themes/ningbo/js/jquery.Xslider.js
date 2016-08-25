(function (a) {
    a.fn.Xslider = function (m) {
        var e = {
            affect: "scrollx",
            speed: 1000,
            space: 5000,
            auto: true,
            trigger: "mouseover",
            conbox: ".pb_slider_con_box",
            ctag: "a",
            switcher: ".pb_slider_switcher",
            stag: "a",
            current: "cur",
            rand: false
        };
        e = a.extend({}, e, m);
        var i = 1;
        var f = 0;
        var b = a(this).find(e.conbox),
            l = b.find(e.ctag);
        var d = a(this).find(e.switcher),
            j = d.find(e.stag);
        if (e.rand) {
            i = Math.floor(Math.random() * l.length);
            g()
        }
        if (e.affect == "fade") {
            a.each(l, function (o, n) {
                (o === 0) ? a(this).css({
                    position: "absolute",
                    "z-index": 9
                }) : a(this).css({
                    position: "absolute",
                    "z-index": 1,
                    opacity: 0
                })
            })
        }

        function g() {
            if (i >= l.length) {
                i = 0
            }
            j.removeClass(e.current).eq(i).addClass(e.current);
            switch (e.affect) {
            case "scrollx":
                b.width(l.length * l.width());
                b.stop().animate({
                    left: -l.width() * i
                }, e.speed);
                break;
            case "scrolly":
                l.css({
                    display: "block"
                });
                b.stop().animate({
                    top: -l.height() * i + "px"
                }, e.speed);
                break;
            case "fade":
                l.eq(f).stop().animate({
                    opacity: 0
                }, e.speed / 2).css("z-index", 1).end().eq(i).css("z-index", 9).stop().animate({
                    opacity: 1
                }, e.speed / 2);
                break;
            case "none":
                l.hide().eq(i).show();
                break
            }
            f = i;
            i++
        }
        if (e.auto) {
            var k = setInterval(g, e.space)
        }
        j.bind(e.trigger, function () {
            c();
            i = a(this).index();
            g();
            h()
        });
        b.hover(c, h);

        function c() {
            clearInterval(k)
        }

        function h() {
            if (e.auto) {
                k = setInterval(g, e.space)
            }
        }
    }
})(jQuery);