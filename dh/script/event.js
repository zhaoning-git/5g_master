var zXml = {
    useActiveX: (typeof ActiveXObject != "undefined"),
    useXmlHttp: (typeof XMLHttpRequest != "undefined")
};

zXml.ARR_XMLHTTP_VERS = ["MSXML2.XmlHttp.6.0", "MSXML2.XmlHttp.3.0"];

function zXmlHttp() { }

zXmlHttp.createRequest = function () {
    if (zXml.useXmlHttp) return new XMLHttpRequest();

    if (zXml.useActiveX)  //IE < 7.0 = use ActiveX
    {
        if (!zXml.XMLHTTP_VER) {
            for (var i = 0; i < zXml.ARR_XMLHTTP_VERS.length; i++) {
                try {
                    new ActiveXObject(zXml.ARR_XMLHTTP_VERS[i]);
                    zXml.XMLHTTP_VER = zXml.ARR_XMLHTTP_VERS[i];
                    break;
                } catch (oError) { }
            }
        }
        if (zXml.XMLHTTP_VER) return new ActiveXObject(zXml.XMLHTTP_VER);
    }
    alert("对不起，您的电脑不支持 XML 插件，请安装好或升级浏览器。");
};

//定义namespace
var _glflash = new Object();
//公共变量
_glflash.Domain = "$$";
_glflash.DataType = "!";
_glflash.SplitRecord = "^";
_glflash.SplitColumn = ",";
var lang = 0;


//通用列表类
_glflash.List = function () {
    this.items = new Array();
    this.keys = new Object();

    this.Add = function (key, value) {
        if (typeof (key) != "undefined") {
            var vv = typeof (value) == "undefined" ? null : value;
            var idx = this.keys[key];
            if (idx == null) {
                idx = this.items.length;
                this.keys[key] = idx;
            }
            this.items[idx] = vv;
        }
    }
    this.Get = function (key) {
        var idx = this.keys[key];
        if (idx != null)
            return this.items[idx];
        return null;
    }
    this.GetNum = function (key) {
        var i = 0;
        for (var k in this.keys) {
            if (key == k)
                return i;
            i++;
        }
        return null;
    }
    this.Clear = function () {
        for (var k in this.keys) {
            delete this.keys[k];
        }
        delete this.keys;
        this.keys = null;
        this.keys = new Object();

        for (var i = 0; i < this.items.length; i++) {
            delete this.items(i);
        }
        delete this.items;
        this.items = null;
        this.items = new Array();
    }
}
_glflash.schedule = function (infoStr) {
    var arr = infoStr.split(_glflash.SplitRecord);
    this.sId = arr[0];
    this.weather = arr[1];
    this.temperature = arr[2];
    this.filed = arr[3].split(_glflash.SplitColumn);
    this.homeTeamID = arr[4];
    this.guestTeamID = arr[5];
    this.homeScore = arr[6];
    this.guestScore = arr[7];
    this.state = arr[8];
    this.jsqScheduleCount = arr[9];
    this.time = arr[10];
    this.detailTime = arr[11];
}
//动态图
_glflash.GraphData = function (sId, infoStr) {
    this.sId = sId;
    var infoArr = infoStr.split(_glflash.SplitColumn);
    this.Id = infoArr[0];
    this.teamId = infoArr[1];
    this.eventType = infoArr[2];
    this.location = infoArr[3];
    this.state = infoArr[4];
    this.time = infoArr[5];
    this.injuryTime = infoArr[6];
}
//事件进度条
_glflash.barDetail = function (infoStr) {
    var arr = infoStr.split(_glflash.SplitColumn);
    this.Id = arr[0];
    this.dataType = arr[1];
    this.teamID = arr[2];
    this.eventType = arr[3];
    this.time = arr[4];
}
_glflash.statusBar = function (sId, infoStr) {
    infoStr = infoStr.replace("\n", "");
    this.sId = sId;
    var arr = infoStr.split(_glflash.SplitRecord);
    this.barList1 = new _glflash.List();
    this.barList2 = new _glflash.List();
    this.barList3 = new _glflash.List();
    var barItem;
    for (var i = 0; i < arr.length; i++) {
        barItem = new _glflash.barDetail(arr[i]);
        var detailArr = arr[i].split(_glflash.SplitColumn)
        if (detailArr.length > 2) {
            if (detailArr[1] == "1")//危险进攻（20）、射门（28，20）
                this.barList1.Add(detailArr[0], barItem);
            else if (detailArr[1] == "2")//角球
                this.barList2.Add(detailArr[0], barItem);
            else//进球
                this.barList3.Add(detailArr[0], barItem);
        }
    }
}
var flashData = new Object();
flashData.scheduleList = new _glflash.List();
flashData.graphList = new _glflash.List();
flashData.statusList = new _glflash.List();

var flashScheduleID = "";
function openFlash(id) {
    autoAnalyID = 0;//将自动展开的动态分析取消
    if (flashScheduleID != "")
        closeFlash(flashScheduleID);
    flashScheduleID = id; 
    var homeScore = 0;//自行获取相关页面上的比分
    var guestScore = 0;
    loadFlashData();
    document.getElementById("flashLive").innerHTML = showFlashLive(flashScheduleID);
    runEvent(flashScheduleID, homeScore, guestScore, null, 0);
    getflashChange();
}
function closeFlash(id) {
    window.clearTimeout(flashTimer); 
    document.getElementById("flashLive").innerHTML = "";   
    flashData = new Object();
    flashData.scheduleList = new _glflash.List();
    flashData.graphList = new _glflash.List();
    flashData.statusList = new _glflash.List();
    oldCornerTime_H = 0, oldCornerTime_G = 0;
    flashScheduleID = "";
}
function loadFlashData() {
    var oXmlFlashHttp = zXmlHttp.createRequest();
    oXmlFlashHttp.open("get", "http://interface.win007.com/zq/dt/demo/flashdata/" + flashScheduleID + ".js?t=" + Date.parse(new Date()), false);
    oXmlFlashHttp.send(null);
    var data = oXmlFlashHttp.responseText;
    var doMains = data.split(_glflash.Domain);
    for (var i = 0; i < doMains.length; i++) {
        var oneSchedule = doMains[i].split(_glflash.DataType);
        var arrSchedule = oneSchedule[0].split(_glflash.SplitRecord);
        var scheduleDetail = new _glflash.schedule(oneSchedule[0]);
        var oneflash = new _glflash.GraphData(arrSchedule[0], oneSchedule[1]);
        var oneStatus = new _glflash.statusBar(arrSchedule[0], oneSchedule[2]);
        flashData.scheduleList.Add(arrSchedule[0], scheduleDetail);       
        flashData.graphList.Add(arrSchedule[0], oneflash);
        flashData.statusList.Add(arrSchedule[0], oneStatus);
    }
}
function showFlashLive(sId) {
    var html = new Array();
    html.push('<div class="ant">');
    html.push('<div class="liveBox">');
    html.push(makeFlashEvent(sId));
    html.push('</div>');
    html.push(makeStatus(sId));
    html.push('</div>');
    return html.join("");
}
function makeFlashEvent(sId) {
    var scheduleDetail = flashData.scheduleList.Get(sId);
    var homeTeam = getTeamHtmlName(sId, 0);
    var guestTeam = getTeamHtmlName(sId, 1);
    var html = new Array();
    html.push('<div class="homeEventBox" id="homeEventBox_' + sId + '">');
    html.push('<div class="team"><div class="teamName">' + homeTeam);
    html.push('</div><div class="ball">控球</div>');
    html.push('</div>');
    html.push('<div class="ctrlBG" id="homeCtrlBG_' + sId + '"></div>');
    html.push('<div class="attackBG" id="homeAttackBG_' + sId + '"></div>');
    html.push('<div class="DAttackBG" id="homeDAttackBG_' + sId + '"></div>');
    html.push('</div>');

    html.push('<div class="guestEventBox" id="guestEventBox_' + sId + '">');
    html.push('<div class="team"><div class="teamName">' + guestTeam);
    html.push('</div><div class="ball">控球</div>');
    html.push('</div>');
    html.push('<div class="ctrlBG" id="guestCtrlBG_' + sId + '"></div>');
    html.push('<div class="attackBG" id="guestAttackBG_' + sId + '"></div>');
    html.push('<div class="DAttackBG" id="guestDAttackBG_' + sId + '"></div>');
    html.push('</div>');

    html.push('<div class="foul" id="foul_' + sId + '">');
    html.push('<span class="redCard"></span>');
    html.push('</div>');

    html.push('<div class="pointBall_0" id="pointBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">' + (lang == 1 ? "點球" : "点球") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="ballIn" id="ballIn_' + sId + '"></div>');

    html.push('<div class="autoBall_0" id="autoBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">任意球</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="DAutoBall_0" id="DAutoBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">' + (lang == 1 ? "危險任意球" : "危险任意球") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="DBall_0" id="DBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">' + (lang == 1 ? "球門球" : "球门球") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="offside_1" id="offside_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">越位</div>');
    html.push('</div>');
    html.push('<i></i>');
    html.push('</div>');

    html.push('<div class="cornerBall_0" id="cornerBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">角球</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="lineBall_0" id="lineBall_' + sId + '">');
    html.push('<div class="team">');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">界外球</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="star" id="star_' + sId + '">');
    html.push('<div class="team"> ');
    html.push('<div class="teamName"></div>');
    html.push('<div class="ball">' + (lang == 1 ? "先開球" : "先开球") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div id="msg_' + sId + '" class="msg"></div>');
    html.push('<div id="shotIn_' + sId + '" class="shotIn">');
    html.push('<div class="team"><span class="teamName"></span>');
    html.push('<div class="ball">' + (lang == 1 ? "射正球門" : "射正球门") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div id="stopIt_' + sId + '" class="stopIt">');
    html.push('<div class="team"><span class="teamName"></span>');
    html.push('<div class="ball">' + (lang == 1 ? "射門被阻擋" : "射门被阻挡") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div id="shotOut_' + sId + '" class="shotOut">');
    html.push('<div class="team"><span class="teamName"></span>');
    html.push('<div class="ball">' + (lang == 1 ? "射偏球門" : "射偏球门") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div id="shotLost_' + sId + '" class="shotLost">');
    html.push('<div class="team"><span class="teamName"></span>');
    html.push('<div class="ball">' + (lang == 1 ? "射中門框" : "射中门框") + '</div>');
    html.push('</div>');
    html.push('</div>');

    html.push('<div class="default" id="default_' + sId + '">');
    html.push('<div class="homeTeam" title="' + homeTeam + '">' + homeTeam + '</div>');
    html.push('<div class="guestTeam" title="' + guestTeam + '">' + guestTeam + '</div>');
    html.push('<div class="tianqi">');
    if (scheduleDetail.temperature != '' || scheduleDetail.filed[0].length > 3 || scheduleDetail.weather != "") {
        html.push('<div class="data2">');
        if (scheduleDetail.filed[0].length > 3) {
            html.push((lang == 1 ? "場地" : "场地") + ' : ' + (lang == 1 ? scheduleDetail.filed[1] : scheduleDetail.filed[0]));
            html.push('<br />');
        }
        if (scheduleDetail.weather != "")
            html.push('天' + (lang == 1 ? "氣" : "气") + ' : ' + scheduleDetail.weather + ' ');
        if (scheduleDetail.temperature != "")
            html.push((lang == 1 ? "溫" : "温") + '度 : ' + scheduleDetail.temperature);
        html.push('</div>');
    }
    html.push('</div>');
    html.push('</div>');
    return html.join("");
}
var flashMsg = new Array(39);
flashMsg[2] = "上半场结束,上半場結束,上半场结束".split(',');
flashMsg[3] = "下半场开始,下半場開始,下半场开始".split(',');
flashMsg[4] = "完场,完場,完场".split(',');
flashMsg[5] = "受伤,受傷,受伤".split(',');
flashMsg[6] = "加时上半场,加時上半場,加时上半场".split(',');
flashMsg[7] = "加时半场,加時半場,加时半场".split(',');
flashMsg[8] = "加时下半场,加時下半場,加时下半场".split(',');
flashMsg[9] = "加时完场,加時完場,加时完场".split(',');
flashMsg[10] = "点球决胜,點球決勝,点球决胜".split(',');
flashMsg[30] = "替补,替補,替补".split(',');
flashMsg[36] = "射失点球,射失點球,射失点球".split(',');
flashMsg[37] = "犯规,犯規,犯规".split(',');
flashMsg[38] = "进球无效,進球無效,进球无效".split(',');
function runEvent(sId, homeScore, guestScore, oneflash, flashNum) {
    if (oneflash != null)
        flashData.graphList.items[flashNum] = oneflash;
    var list = flashData.graphList.Get(sId);
    var scheduleDetail = flashData.scheduleList.Get(sId);
    var teamType = scheduleDetail.homeTeamID == list.teamId ? 0 : 1;
    if (scheduleDetail.state == 0)
        defaultInfo(sId);
    else {
        switch (parseInt(list.eventType)) {
            case 1:
                star(sId, teamType);
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 30:
            case 37:
            case 38:
                showMsg(sId, flashMsg[list.eventType][lang]);
                break;
            case 20:
                dangerousAttack(sId, teamType);
                break;
            case 21:
                attack(sId, teamType);
                break;
            case 22:
                ctrl(sId, teamType);
                break;
            case 23:
                ballIn(sId, teamType, scheduleDetail.homeScore, scheduleDetail.guestScore);
                break;
            case 24:
            case 25:
                var cardType = list.eventType == 25 ? 'red' : 'yellow';
                foul(sId, cardType, teamType);
                break;
            case 26:
                DBall(sId, teamType);
                break;
            case 27:
                pointBall(sId, teamType);
                break;
            case 28:
                shotIn(sId, teamType);
                break;
            case 29:
                shotOut(sId, teamType);
                break;
            case 31:
                offside(sId, teamType);
                break;
            case 32:
                autoBall(sId, teamType);
                break;
            case 33:
                lineBall(sId, list.location, teamType);
                break;
            case 34:
                var direction;
                if (list.location > 0) {           
                    if (teamType == 0)
                        direction = list.location == 1 ? 1 : 2;
                    else
                        direction = list.location == 1 ? 0 : 3;
                }
                else {
                    direction = teamType == 0 ? 1 : 3;
                }
                cornerBall(sId, direction, teamType);
                break;
            case 35:
                pointBallIn(sId, teamType, scheduleDetail.homeScore, scheduleDetail.guestScore);
                break;
            case 36:
                showPointBallMsg(sId, flashMsg[36][lang]);
                break;
            case 39:
                DAutoBall(sId, teamType);
                break;
            case 40:
                stopIt(sId, teamType);
                break;
            case 41:
                shotLost(sId, teamType);
                break;
            case 42:
                var msg = (list.injuryTime > 0 ? (lang == 1 ? "補時" : "补时") + list.injuryTime + (lang == 1 ? "分鐘" : "分钟") : (lang == 1 ? "傷停補時" : "伤停补时"));
                showMsg(sId, msg);
                break;
        }
    }
}
function setStatusTimeLine(minutes, state, sId) {
    if (state == "2") minutes = 45;
    if (state == "-1" || parseInt(state) > 3) minutes = 90;
    if (state == "-1" && minutes > 45) minutes = 45;
    if (minutes > 95) minutes = 95;
    try {
        document.getElementById("timeLine_" + sId).style.left = getStatusPosition(0, minutes) + "px";
    }
    catch (e) { }
}
var oldCornerTime_H = 0, oldCornerTime_G = 0;
function makeStatus(sId) {
    var html = new Array();
    var homeHtml = new Array();
    var guestHtml = new Array();
    var oneStatus = flashData.statusList.Get(sId);
    var scheduleDetail = flashData.scheduleList.Get(sId);
    var state = parseInt(scheduleDetail.state);
    var listGraph = oneStatus.barList1;//20 a; 28,29 s;
    var listCorner = oneStatus.barList2;//f
    var listGoal = oneStatus.barList3;//b
    html.push('<div class="timeLine' + (lang == 1 ? " big" : "") + '" id="statusLine_' + sId + '">');
    html.push('<div class="info">');
    if (state >= -1) {
        var minutes = $("#time_" + sId).text();
        if (state == 2) minutes = 45;
        if (state == -1 || state > 3) minutes = 90;
        if (minutes == '90+') minutes = 90;
        if (state == 1 && minutes != '' && minutes == '45+') minutes = 45;
        if (minutes == "中") minutes = 45;
        html.push('<div class="timeLine" id="timeLine_' + sId + '" style="left:' + getStatusPosition(0, minutes) + 'px"></div>');// 
    }
    for (var i = 0; i < listGraph.items.length; i++) {
        var num = getStatusPosition(listGraph.items[i].eventType, listGraph.items[i].time);
        var onei = '<i class="' + (listGraph.items[i].eventType == "20" ? "a" : "s") + '" style="left:' + num + 'px;cursor:pointer;" title="' + listGraph.items[i].time + '\'"></i>';
        if (scheduleDetail.homeTeamID == listGraph.items[i].teamID)
            homeHtml.push(onei);
        else
            guestHtml.push(onei);
    }
    for (var i = 0; i < listCorner.items.length; i++) {
        var num = getStatusPosition(listCorner.items[i].eventType, listCorner.items[i].time);
        if (scheduleDetail.homeTeamID == listCorner.items[i].teamID) {
            if (num - oldCornerTime_H <= 3)
                num += 3 - (num - oldCornerTime_H);
            var onei = '<i class="f" style="left:' + num + 'px;cursor:pointer;" title="' + listCorner.items[i].time + '\'"></i>';
            homeHtml.push(onei);
            oldCornerTime_H = num;
        }
        else {
            if (num - oldCornerTime_G <= 3)
                num += 3 - (num - oldCornerTime_G);
            var onei = '<i class="f" style="left:' + num + 'px;cursor:pointer;" title="' + listCorner.items[i].time + '\'"></i>';
            guestHtml.push(onei);
            oldCornerTime_G = num;
        }
    }
    for (var i = 0; i < listGoal.items.length; i++) {
        var num = getStatusPosition(listGoal.items[i].eventType, listGoal.items[i].time);
        var onei = '<i class="b" style="left:' + num + 'px;cursor:pointer;" title="' + listGoal.items[i].time + '\'"></i>';
        if (scheduleDetail.homeTeamID == listGoal.items[i].teamID)
            homeHtml.push(onei);
        else
            guestHtml.push(onei);
    }
    html.push('<div class="home" id="homeLine_' + sId + '">');
    html.push(homeHtml.join(""));
    html.push('</div>');
    html.push('<div class="guest" id="guestLine_' + sId + '">');
    html.push(guestHtml.join(""));
    html.push('</div>');
    html.push('</div>');
    html.push('</div>');
    return html.join("");
}
function getStatusPosition(t, time) {
    var imgWidth = 0;
    switch (parseInt(t)) {
        case 20:
        case 28:
        case 29:
            imgWidth = 2;
            break;
        case 1:
        case 7:
        case 8:
        case 9:
            imgWidth = 14;
            break;
        case 0:
            imgWidth = 7;
            break;
    }
    return parseInt(time / 90 * 277 - imgWidth / 2);
}
var xmlFlash = zXmlHttp.createRequest();
var oldFlash = "";
function getflashChange() {
    try {
        xmlFlash.open("get", "flashdata/" + flashScheduleID + "_ch.js?t=" + Date.parse(new Date()), true);
        xmlFlash.onreadystatechange = flashRefresh;
        xmlFlash.send(null);
    } catch (e) { }
    flashTimer = window.setTimeout("getflashChange()", 2000);
}
var __sto = setTimeout;
window.setTimeout2 = function (callback, timeout, param) {
    var args = Array.prototype.slice.call(arguments, 2);
    var _cb = function () {
        callback.apply(null, args);
    }
    __sto(_cb, timeout);
}
var oldXML="";
function flashRefresh() {
    if (xmlFlash.readyState != 4 || (xmlFlash.status != 200 && xmlFlash.status != 0)) return;
    if (oldXML == xmlFlash.responseText) return
    oldXML = xmlFlash.responseText;
    var arr;
    var changeIDList = ",";
    var playFlash = false;
    if (xmlFlash.responseText == null || xmlFlash.responseText.replace("\n", "") == "") return;
    var data = xmlFlash.responseText.replace("\n", "");
    var doMains = data.split(_glflash.Domain);
    for (var i = 0; i < doMains.length; i++) {
        var oneSchedule = doMains[i].split(_glflash.DataType);
        var arrSchedule = oneSchedule[0].split(_glflash.SplitRecord);
        var scheduleDetail = flashData.scheduleList.Get(arrSchedule[0]);
        scheduleDetail.homeScore = parseInt(arrSchedule[1]);
        scheduleDetail.guestScore = parseInt(arrSchedule[2]);
        scheduleDetail.state = parseInt(arrSchedule[3]);
        scheduleDetail.time = arrSchedule[4];
       
        if (typeof (oneSchedule[1]) != "undefined" && oneSchedule[1] != "") {
            var arrLive = oneSchedule[1].split(_glflash.SplitRecord);
            var flashNum = flashData.graphList.GetNum(arrSchedule[0]);
            var oldFlash = flashData.graphList.items[flashNum];
            var startNum = 0;
            for (var j = 0; j < arrLive.length; j++) {
                var onearr = arrLive[j].split(_glflash.SplitColumn);
                if (onearr.length < 3) continue;
                var oneflash = new _glflash.GraphData(arrSchedule[0], arrLive[j]);
                if (oldFlash.Id == "" || (oldFlash.Id != "" && parseInt(oldFlash.Id) < parseInt(oneflash.Id))) {
                    oldFlash = oneflash;
                    var overTime = 500 * startNum;
                    if (oneflash.eventType == 23) {
                        var scheduleDetail = flashData.scheduleList.Get(arrSchedule[0]);
                        scheduleDetail.homeScore = parseInt(onearr[7]);
                        scheduleDetail.guestScore = parseInt(onearr[8]);
                    }
                    window.setTimeout2(runEvent, overTime, oneflash.sId, scheduleDetail.homeScore, scheduleDetail.guestScore, oneflash, flashNum);//避免动画消失太快     
                    startNum++;
                }
            }
        }
        if (typeof (oneSchedule[2]) != "undefined" && oneSchedule[2] != "") {
            var arrStatus = oneSchedule[2].split(_glflash.SplitRecord);
            var statusNum = flashData.statusList.GetNum(arrSchedule[0]);
            var oldStatus = flashData.statusList.items[statusNum];
            for (var j = 0; j < arrStatus.length; j++) {
                var oneStatus = new _glflash.barDetail(arrStatus[j]);
                var onearr = arrStatus[j].split(_glflash.SplitColumn);
                if (onearr.length < 3) continue;
                var oldStatusItem1 = oldStatus.barList1.items[oldStatus.barList1.items.length - 1];
                if (onearr[1] == "1" && (typeof (oldStatusItem1) == "undefined" || (typeof (oldStatusItem1) != "undefined" && parseInt(oldStatusItem1.Id) < parseInt(oneStatus.Id)))) {
                    flashData.statusList.items[statusNum].barList1.Add(arrSchedule[0], oneStatus);
                    var num = getStatusPosition(oneStatus.eventType, oneStatus.time);
                    var onei = '<i class="' + (oneStatus.eventType == "20" ? "a" : "s") + '" style="left:' + num + 'px;cursor:pointer;" title="' + oneStatus.time + '\'"></i>';
                    if (scheduleDetail.homeTeamID == oneStatus.teamID)
                        document.getElementById("homeLine_" + arrSchedule[0]).innerHTML += onei;
                    else
                        document.getElementById("guestLine_" + arrSchedule[0]).innerHTML += onei;
                }
                var oldStatusItem2 = oldStatus.barList2.items[oldStatus.barList2.items.length - 1];
                if (onearr[1] == "2" && (typeof (oldStatusItem2) == "undefined" || (typeof (oldStatusItem2) != "undefined" && parseInt(oldStatusItem2.Id) < parseInt(oneStatus.Id)))) {
                    flashData.statusList.items[statusNum].barList2.Add(arrSchedule[0], oneStatus);
                    var num = getStatusPosition(oneStatus.eventType, oneStatus.time);
                    if (scheduleDetail.homeTeamID == oneStatus.teamID) {
                        if (num - oldCornerTime_H <= 3)
                            num += 3 - (num - oldCornerTime_H);
                        var onei = '<i class="f" style="left:' + num + 'px;cursor:pointer;" title="' + oneStatus.time + '\'"></i>';
                        document.getElementById("homeLine_" + arrSchedule[0]).innerHTML += onei;
                        oldCornerTime_H = num;
                    }
                    else {
                        if (num - oldCornerTime_G <= 3)
                            num += 3 - (num - oldCornerTime_G);
                        var onei = '<i class="f" style="left:' + num + 'px;cursor:pointer;" title="' + oneStatus.time + '\'"></i>';
                        document.getElementById("guestLine_" + arrSchedule[0]).innerHTML += onei;
                        oldCornerTime_G = num;
                    }
                }
                var oldStatusItem3 = oldStatus.barList3.items[oldStatus.barList3.items.length - 1];
                if (onearr[1] == "3" && (typeof (oldStatusItem3) == "undefined" || (typeof (oldStatusItem3) != "undefined" && parseInt(oldStatusItem3.Id) < parseInt(oneStatus.Id)))) {
                    flashData.statusList.items[statusNum].barList3.Add(arrSchedule[0], oneStatus);
                    var num = getStatusPosition(oneStatus.eventType, oneStatus.time);
                    var onei = '<i class="b" style="left:' + num + 'px;cursor:pointer;" title="' + oneStatus.time + '\'"></i>';
                    if (scheduleDetail.homeTeamID == oneStatus.teamID)
                        document.getElementById("homeLine_" + arrSchedule[0]).innerHTML += onei;
                    else
                        document.getElementById("guestLine_" + arrSchedule[0]).innerHTML += onei;
                }
            }
        }
        if (arrSchedule[5] != scheduleDetail.detailTime) {//入球事件有删除时触发
            refreshStatus(arrSchedule[0]);
        }
    }
}
function refreshStatus(sId) {
    loadFlashData();
    oldCornerTime_H = 0, oldCornerTime_G = 0;
    document.getElementById("statusLine_" + sId).innerHTML = makeStatus(sId);
}
//--------------------------------------------动画实现------------------------------------------
var isHomeTeamAttack = false;
var isAttack = false;
var teamType = ["home", "guest"];


var init = function (mid) {
    $(".liveBox div").stop(true, true, true);    
    $("#ballIn_" + mid).fadeOut(300);
    $("#cornerBall_" + mid).fadeOut(300);
    $("#lineBall_" + mid).fadeOut(300);
    $("#pointBall_" + mid).fadeOut(300);
    $("#star_" + mid).fadeOut(300);
    $("#autoBall_" + mid).fadeOut(300);
    $("#DAutoBall_" + mid).fadeOut(300);
    $("#foul_" + mid).fadeOut(300);
    $("#default_" + mid).fadeOut(300);
    $("#msg_" + mid).fadeOut(300);
    $("#shotIn_" + mid).fadeOut(300);
    $("#stopIt_" + mid).fadeOut(300);
    $("#shotOut_" + mid).fadeOut(300);
    $("#shotLost_" + mid).fadeOut(300);
    $("#DBall_" + mid).fadeOut(300);
    $("#offside_" + mid).fadeOut(300);
    if (isHomeTeamAttack || !isAttack) {
        $("#guestEventBox_" + mid).fadeOut(300);
    }
    if (!isHomeTeamAttack || !isAttack) {
        $("#homeEventBox_" + mid).fadeOut(300);
    }
    isHomeTeamAttack = false;
    isAttack = false;

};

function getTeamHtmlName(mid, type) {
    return $("#team" + (type + 1) + "_" + mid).text().replace("(中)", "");
}
var resetTeamPostion = function (mid, eventBox, type, num, index) {
    eventBox.fadeIn();
    if (type == "0") {
        eventBox.find(".team").animate({ right: num });
    } else {
        eventBox.find(".team").animate({ left: num });
    }
    var ctrlBG = $("#" + teamType[type] + "CtrlBG_" + mid);
    var attackBG = $("#" + teamType[type] + "AttackBG_" + mid);
    var DAttackBG = $("#" + teamType[type] + "DAttackBG_" + mid);
    if (index == 0) {
        eventBox.find(".ball").text("控球");
        attackBG.hide();
        DAttackBG.hide();
        ctrlBG.fadeIn();
    } else if (index == 1) {
        eventBox.find(".ball").text("进攻");
        ctrlBG.hide();
        DAttackBG.hide();
        attackBG.fadeIn();
    } else if (index == 2) {
        eventBox.find(".ball").text("危险进攻");
        ctrlBG.hide();
        attackBG.hide();
        DAttackBG.fadeIn();
    }
}

var ctrl = function (mid, type) {
    isHomeTeamAttack = (type == "0");
    isAttack = true;
    init(mid);
    var eventBox = $("#" + teamType[type] + "EventBox_" + mid);
    eventBox.animate({ width: '40%' }, 300);
    resetTeamPostion(mid, eventBox, type, "5px", 0);

};



var attack = function (mid, type) {
    isHomeTeamAttack = (type == "0");
    isAttack = true;
    init(mid);
    var eventBox = $("#" + teamType[type] + "EventBox_" + mid);
    eventBox.animate({ width: '65%' }, 300);
    resetTeamPostion(mid, eventBox, type, "28px", 1);

};



var dangerousAttack = function (mid, type) {
    isHomeTeamAttack = (type == "0");
    isAttack = true;
    init(mid);
    var eventBox = $("#" + teamType[type] + "EventBox_" + mid);
    eventBox.animate({ width: '85%' }, 300);
    resetTeamPostion(mid, eventBox, type, "28px", 2);

};



var ballIn = function (mid, type, homeScore, guestScore) {
    init(mid);
    var ballInObj = $("#ballIn_" + mid);
    ballInObj.attr("class", "ballIn_" + type);
    if (type == '0')//主队进球
    {
        ballInObj.html('<span class="home on">' + homeScore + '</span> - <span class="guest">' + guestScore + '</span>');
    } else {
        ballInObj.html('<span class="home">' + homeScore + '</span> - <span class="guest on">' + guestScore + '</span>');
    }
    ballInObj.show();
};
var foul = function (mid, cradType, type) {
    init(mid);
    var foulObj = $("#foul_" + mid);
    if (cradType == 'red')//主队进球
    {
        foulObj.html(' <span class="redCard">' + getTeamHtmlName(mid, type) + '</span>');
    } else {
        foulObj.html(' <span class="yellowCard">' + getTeamHtmlName(mid, type) + '</span>');
    }
    foulObj.fadeIn();
};


var DBall = function (mid, type) {
    init(mid);
    var autoBallObj = $("#DBall_" + mid);
    autoBallObj.attr("class", "DBall_" + type);
    autoBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    autoBallObj.fadeIn();
};


var autoBall = function (mid, type) {
    init(mid);
    var autoBallObj = $("#autoBall_" + mid);
    autoBallObj.attr("class", "autoBall_" + type);
    autoBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    autoBallObj.fadeIn();
};

var DAutoBall = function (mid, type) {
    init(mid);
    var autoBallObj = $("#DAutoBall_" + mid);
    autoBallObj.attr("class", "DAutoBall_" + type);
    autoBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    autoBallObj.fadeIn();
};

var offside = function (mid, type) {
    init(mid);
    var autoBallObj = $("#offside_" + mid);
    autoBallObj.attr("class", "offside_" + type);
    autoBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    autoBallObj.fadeIn();
};


var cornerBall = function (mid, direction, type) {//direction   0,1,2,3  左上,右上,右下,左下
    init(mid);
    var cornerBallObj = $("#cornerBall_" + mid);
    cornerBallObj.attr("class", "cornerBall_" + direction);
    cornerBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    cornerBallObj.fadeIn();
};


var lineBall = function (mid, postionID, type) {//postionID = 0,1,2,3  分别代表位置级别
    init(mid);
    var lineBallObj = $("#lineBall_" + mid);
    var top_up = (type == 1 && postionID == 0) ? 2 : type;
    lineBallObj.attr("class", "lineBall_" + top_up);
    var p = postionID != 0 ? postionID - 1 : postionID;
    var dir = type == 0 ? "left" : "right";
    var del = type != 0 ? "left" : "right";
    lineBallObj.css(del, "auto");
    lineBallObj.css(dir, (64 + p * 44) + "px");
    lineBallObj.find(".teamName").text(getTeamHtmlName(mid, type));
    lineBallObj.fadeIn();
};


var star = function (mid, type) {
    init(mid);
    var starObj = $("#star_" + mid);
    starObj.find(".teamName").text(getTeamHtmlName(mid, type));
    starObj.fadeIn();
};
var defaultInfo = function (mid) {
    init(mid);
    var defaultObj = $("#default_" + mid);
    defaultObj.fadeIn();
};
var showMsg = function (mid, str) {
    init(mid);
    var defaultObj = $("#msg_" + mid);
    defaultObj.html(str);
    defaultObj.fadeIn();
};


var shotIn = function (mid, type) {
    init(mid);
    var defaultObj = $("#shotIn_" + mid);
    defaultObj.find(".teamName").text(getTeamHtmlName(mid, type));
    defaultObj.fadeIn();
};
var stopIt = function (mid, type) {
    init(mid);
    var defaultObj = $("#stopIt_" + mid);
    defaultObj.find(".teamName").text(getTeamHtmlName(mid, type));
    defaultObj.fadeIn();
};
var shotOut = function (mid, type) {
    init(mid);
    var defaultObj = $("#shotOut_" + mid);
    defaultObj.find(".teamName").text(getTeamHtmlName(mid, type));
    defaultObj.fadeIn();
};
var shotLost = function (mid, type) {
    init(mid);
    var defaultObj = $("#shotLost_" + mid);
    defaultObj.find(".teamName").text(getTeamHtmlName(mid, type));
    defaultObj.fadeIn();
};

var showMsg = function (mid, str) {
    init(mid);
    var defaultObj = $("#msg_" + mid);
    defaultObj.html(str);
    var h = defaultObj.height();
    defaultObj.css("margin-top", 0 - h / 2 + "px");
    defaultObj.fadeIn();
};

var showPointBallMsg = function (mid, str) {
    var defaultObj = $("#msg_" + mid);
    defaultObj.html(str);
    var h = defaultObj.height();
    defaultObj.css("margin-top", 0 - h / 2 + "px");
    defaultObj.fadeIn();
};


var pointBall = function (mid, type) {
    init(mid);
    var pointBall = $("#pointBall_" + mid);
    pointBall.attr("class", "pointBall_" + type);
    pointBall.find(".teamName").text(getTeamHtmlName(mid, type));
    pointBall.fadeIn();

};


var pointBallIn = function (mid, type, homeScore, guestScore) {
    var ballInObj = $("#ballIn_" + mid);
    if (type = '0')//主队进球
    {
        ballInObj.html('<span class="home on">' + homeScore + '</span> - <span class="guest">' + guestScore + '</span>');
    } else {
        ballInObj.html('<span class="home">' + homeScore + '</span> - <span class="guest on">' + guestScore + '</span>');
    }
    ballInObj.show();
};

