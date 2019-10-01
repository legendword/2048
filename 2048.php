<!DOCTYPE HTML>
<html>

<head>
    <title>2048 - Legendword</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="dependencies/jquery-1.10.2.js"></script>
    <!-- <link rel="stylesheet" href="http://legendword.com/files/main.css"> -->
    <link rel="stylesheet" href="dependencies/bootstrap.min.css">
    <script src="dependencies/bootstrap.min.js"></script>
    <script>
    var core = {
        setup: function() {
            var al = 0;
            for (var i = 0; i < 4; i++) {
                var nl = document.createElement("div");
                document.getElementById("main").appendChild(nl);
                nl.setAttribute("class", "g-row");
                nl.setAttribute("id", "row-" + i);
                if (i == 3) { nl.setAttribute("data-last", "true"); }
                for (var x = 0; x < 4; x++) {
                    var nd = document.createElement("span");
                    nl.appendChild(nd);
                    nd.setAttribute("class", "tile tile-0");
                    if (x == 3) { nd.setAttribute("data-last", "true"); }
                    var td = document.createElement("span");
                    nd.appendChild(td);
                    td.setAttribute("class", "text");
                    td.innerHTML = "&nbsp;";
                }
            }
            //initialize game start
            core.game.initialize();
            //get best score
            //if not set, create one
            if (localStorage.getItem("2048-bestScore") == null || isNaN(localStorage.getItem("2048-bestScore"))) {
                localStorage.setItem("2048-bestScore", 0);
            }
            $("#best").text(localStorage.getItem("2048-bestScore"));
        },
        game: {
            over: function() {
                alert("Game Over!\nYour score is: " + core.score);
                core.gameStatus = 0;
            },
            initialize: function() {
                core.game.generate(2);
            },
            generate: function(count) {
                while (count > 0) {
                    if (core.tileCount >= 16) {
                        var nostep = true;
                        for (var i = 1; i <= 4; i++) {
                            for (var j = 1; j <= 3; j++) {
                                if (core.tiles[core.game.find(i, j)].value == core.tiles[core.game.find(i, j + 1)].value) {
                                    nostep = false;
                                    break;
                                }
                                if (core.tiles[core.game.find(j, i)].value == core.tiles[core.game.find(j + 1, i)].value) {
                                    nostep = false;
                                    break;
                                }
                            }
                            if (!nostep) break;
                        }
                        if (nostep) {
                            setTimeout(core.game.over, 2000);
                        }
                        return;
                    }
                    var found = false;
                    while (!found) {
                        var x = Math.floor(Math.random() * 4) + 1,
                            y = Math.floor(Math.random() * 4) + 1;
                        found = true;
                        for (var d of core.tiles) {
                            if (d.state != 0 && d.x == x && d.y == y) {
                                found = false;
                                break;
                            }
                        }
                    }
                    var rnum = Math.floor(Math.random() * 100); //possibility of 4 or 2
                    core.game.newTile(x, y, rnum > 80 ? 4 : 2);
                    count--;
                }
            },
            newTile: function(x, y, d) {
                var nt = new Tile(x, y, d, 1);
                var i;
                for (i = 0; i < core.tiles.length; i++) {
                    if (core.tiles[i].state == 0) {
                        core.tiles[i] = nt;
                        break;
                    }
                }
                if (i == core.tiles.length) core.tiles.push(nt);
                $("#insertLabel").after($('<span class="tile tile-' + d + ' gametile" id="tile-' + i + '" style="opacity:0;position:absolute;top:' + core.game.cal(x) + 'px;left:' + core.game.cal(y) + 'px;"><span class="text">' + d + '</span></span>'));
                $("#tile-" + i).animate({ opacity: 1 }, { duration: 200 });
                core.tileCount++;
                return nt;
            },
            cal: function(n) {
                return 15 + 110 * (n - 1);
            },
            find: function(x, y) {
                for (var i = 0; i < core.tiles.length; i++) {
                    if (core.tiles[i].state != 0 && core.tiles[i].x == x && core.tiles[i].y == y) return i;
                }
                return -1;
            },
            updateTile: function(ti) {
                $("#tile-" + ti + ">span").text(core.tiles[ti].value);
                if (core.tiles[ti].value <= 2048) $("#tile-" + ti).attr("class", "tile tile-" + core.tiles[ti].value + " gametile");
                else {
                    $("#tile-" + ti).attr("class", "tile tile-super gametile");
                }
            },
            nextStep: function() {
                var rd = core.removeList.pop();
                while (rd != null) {
                    $("#tile-" + rd).remove();
                    rd = core.removeList.pop();
                }
                var cm = Math.ceil(Math.random()*100);
                core.game.generate((cm<=30?2:1));
            },
            move: function(code) {
                if (core.gameStatus == 0) return;
                var moved = false;
                for (var d of core.tiles) {
                    if (d.state == 2) d.state = 1; //reset state
                }
                var dx, dy;
                switch (code) {
                    case 38:
                        dx = -1;
                        dy = 0;
                        break;
                    case 37:
                        dx = 0;
                        dy = -1;
                        break;
                    case 39:
                        dx = 0;
                        dy = 1;
                        break;
                    case 40:
                        dx = 1;
                        dy = 0;
                        break;
                    default:
                        break;
                }
                var cx = dx == 1 ? 4 : 1,
                    cy = dy == 1 ? 4 : 1;
                var i = cx,
                    j = cy;
                while (true) {
                    j = cy;
                    while (true) {
                        var cur = core.game.find(i, j);
                        if (cur != -1) {
                            var tx = i,
                                ty = j,
                                step = false;
                            while (true) {
                                tx += dx;
                                ty += dy;
                                if (tx < 1 || tx > 4 || ty < 1 || ty > 4) break;
                                var d = core.game.find(tx, ty);
                                if (d != -1) {
                                    if (core.tiles[d].value == core.tiles[cur].value && core.tiles[d].state == 1) {
                                        //console.log("merge animate ",cur," to ",d,", ",tx,",",ty);
                                        $("#tile-" + cur).animate({ top: core.game.cal(tx) + "px", left: core.game.cal(ty) + "px" }, { duration: 200 });
                                        core.removeList.push(d);
                                        core.tileCount--;
                                        core.tiles[d].state = 0;
                                        core.tiles[cur].state = 2;
                                        core.tiles[cur].value *= 2;
                                        core.tiles[cur].x = tx;
                                        core.tiles[cur].y = ty;
                                        core.game.updateTile(cur);
                                        core.addScore(core.tiles[cur].value);
                                        moved = true;
                                    }
                                    break;
                                } else {
                                    step = true;
                                    core.tiles[cur].x = tx;
                                    core.tiles[cur].y = ty;
                                }
                            }
                            if (step) {
                                moved = true;
                                $("#tile-" + cur).animate({ top: core.game.cal(core.tiles[cur].x) + "px", left: core.game.cal(core.tiles[cur].y) + "px" }, { duration: 200 });
                            }
                        }
                        j += dy == 1 ? -1 : 1;
                        if (j < 1 || j > 4) break;
                    }
                    i += dx == 1 ? -1 : 1;
                    if (i < 1 || i > 4) break;
                }
                if (moved) setTimeout(core.game.nextStep, 200);
            }
        },
        removeList: [],
        tiles: [],
        tileCount: 0,
        temp: 0,
        score: 0,
        gameStatus: 1,
        addScore: function(u) {
            if (core.score == null || isNaN(core.score)) { core.score = 0; return false; }
            core.score += u;
            if (core.score > parseInt(localStorage.getItem("2048-bestScore"))) {
                localStorage.setItem("2048-bestScore", core.score);
                $("#best").text(core.score);
            }
            $("#score").text(core.score);
        }
    };
    window.onload = function() {
        core.setup();
        document.body.onkeydown = function(e) {
            if (e.keyCode > 36 && e.keyCode < 41) {
                core.game.move(e.keyCode);
            }
        }
    }

    function Tile(a, b, c, d) {
        this.x = a;
        this.y = b;
        this.value = c;
        this.state = d; //state 0: removed, 1: active, 2: merged once
    }
    </script>
    <style>
    .tile-0 {
        background-color: #CDC1B4;
    }

    .tile-2 {
        background-color: #EEE4DA;
    }

    .tile-4 {
        background-color: #EDE0C8;
    }

    .tile-8 {
        background-color: #F2B179;
    }

    .tile-16 {
        background-color: #F59563;
    }

    .tile-32 {
        background-color: #F67C5F;
    }

    .tile-64 {
        background-color: #F65E3B;
    }

    .tile-128 {
        background-color: #EDCF72;
    }

    .tile-256 {
        background-color: #EDCC61;
    }

    .tile-512 {
        background-color: #EDC850;
    }

    .tile-1024 {
        background-color: #EDC53f;
    }

    .tile-2048 {
        background-color: #EDC22E;
    }

    .tile-super {
        background-color: #3C3A32;
    }

    .tile-1>span,
    .tile-2>span {
        color: #776E65;
    }

    .text {
        font-size: 50px;
        font-weight: bold;
    }

    .tile-128>.text,
    .tile-256>.text,
    .tile-512>.text {
        font-size: 45px;
    }

    .tile-1024>.text,
    .tile-2048>.text,
    .tile-super>.text {
        font-size: 35px;
    }

    .tile {
        -webkit-transition: 100ms ease-in-out;
        -moz-transition: 100ms ease-in-out;
        transition: 100ms ease-in-out;
        -webkit-transition-property: -webkit-transform;
        -moz-transition-property: -moz-transform;
        transition-property: transform;
        border-radius: 5px;
    }

    .text.new {
        -webkit-animation: appear 200ms ease 100ms;
        -moz-animation: appear 200ms ease 100ms;
        animation: appear 200ms ease 100ms;
        -webkit-animation-fill-mode: backwards;
        -moz-animation-fill-mode: backwards;
        animation-fill-mode: backwards;
    }

    .tile.tile-left {
        -webkit-transform: translate(-110px, 0px);
        -moz-transform: translate(-110px, 0px);
        transform: translate(-110px, 0px);
    }

    body {
        -user-select: none;
        -webkit-user-select: none;
        -o-user-select: none;
        -moz-user-select: none;
        cursor: default;
        overflow: hidden;
    }

    #main>.g-row>span,
    .gametile {
        margin-right: 10px;
        height: 100px;
        width: 100px;
        display: inline-block;
        text-align: center;
        line-height: 100px;
        color: #F9F6F2;
    }

    #main>.g-row {
        margin-bottom: 10px;
        height: 100px;
        line-height: 100px;
    }

    #main>.g-row>span[data-last=true] {
        margin-right: 0;
    }

    #main>.g-row[data-last=true] {
        margin-bottom: 0;
    }

    #main {
        text-align: center;
        margin: auto;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 460px;
        height: 460px;
        padding: 15px;
        border-radius: 5px;
        background-color: #BBADA0;
    }

    #scores {
        position: absolute;
        left: 50px;
        top: 0;
        bottom: 0;
        margin: auto;
        width: 200px;
        height: 150px;
    }

    #options {
        display: none;
    }

    #score,
    #best {
        font-size: 25px;
    }

    /* main background: BBADA0
   tile background: CDC1B4
   font color (2,4): 776E65
   font color (other): F9F6F2
 */
    </style>
</head>

<body>
    <div class="container-fluid">
        <div id="game">
            <div id="scores">
                <h5>Score</h5>
                <p id="score">0</p>
                <h5>High</h5>
                <p id="best">0</p>
            </div>
            <div id="main">
                <span id="insertLabel"></span>
            </div>
        </div>
    </div>
</body>

</html>