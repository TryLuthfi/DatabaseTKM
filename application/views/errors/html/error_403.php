<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: Arial, Helvetica, sans-serif;
      background: #f7f6f3;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #222;
    }
    .wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
    }
    .scene {
      position: relative;
      width: 320px;
      height: 220px;
      margin-bottom: 2rem;
    }
    .ground {
      position: absolute;
      bottom: 18px; left: 20px; right: 20px; height: 18px;
      background: #e8e5df;
      border-radius: 50%;
      filter: blur(6px);
      opacity: 0.7;
    }
    .hole {
      position: absolute;
      bottom: 22px;
      left: 50%; transform: translateX(-50%);
      width: 110px; height: 26px;
      background: #c0b8b0;
      border-radius: 50%;
      opacity: 0.55;
    }
    .num {
      position: absolute;
      font-size: 86px;
      font-weight: 900;
      line-height: 1;
      letter-spacing: -4px;
      font-family: 'Arial Black', Arial, sans-serif;
    }
    .num-5 { bottom: 26px; left: 52px;  color: #e2765a; transform: rotate(-8deg); }
    .num-0 { bottom: 28px; left: 118px; color: #e07f68; }
    .num-3 { bottom: 24px; left: 182px; color: #e2765a; transform: rotate(6deg); }
    .pole {
      position: absolute;
      bottom: 36px; left: 90px;
      width: 6px; height: 130px;
      background: #888;
      border-radius: 3px;
    }
    .sign {
      position: absolute;
      bottom: 158px; left: 50px;
      width: 60px; height: 60px;
      background: #e05252;
      clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .sign span {
      color: #fff;
      font-weight: 900;
      font-size: 9.5px;
      letter-spacing: 0.5px;
      font-family: 'Arial Black', Arial, sans-serif;
    }
    .bldg {
      position: absolute;
      bottom: 36px;
      background: #e0dbd4;
      border-radius: 2px 2px 0 0;
      opacity: 0.35;
    }
    .bldg-win {
      position: absolute;
      width: 7px; height: 9px;
      background: #c5c0b8;
      border-radius: 1px;
    }
    .caution {
      position: absolute;
      bottom: 26px; right: 58px;
      width: 38px; height: 62px;
    }
    .caution-body {
      width: 100%; height: 100%;
      background: #e8c46a;
      border-radius: 4px 4px 2px 2px;
      position: relative;
      overflow: hidden;
    }
    .caution-stripe {
      position: absolute;
      left: 0; right: 0;
      height: 7px;
      background: #222;
      opacity: 0.18;
    }
    .caution-leg {
      position: absolute;
      bottom: -6px;
      width: 8px; height: 10px;
      background: #c9a84c;
      border-radius: 0 0 3px 3px;
    }
    .caution-leg.l { left: 4px; }
    .caution-leg.r { right: 4px; }
    .maint-title {
      font-size: 28px;
      font-weight: 700;
      color: #222;
      letter-spacing: 3px;
      text-transform: uppercase;
      margin: 0 0 10px;
    }
    .maint-sub {
      font-size: 15px;
      color: #888;
      text-align: center;
      line-height: 1.6;
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="scene">
    <div class="bldg" style="right:12px;width:48px;height:110px;">
      <div class="bldg-win" style="top:12px;left:6px;"></div>
      <div class="bldg-win" style="top:12px;right:6px;"></div>
      <div class="bldg-win" style="top:32px;left:6px;"></div>
      <div class="bldg-win" style="top:32px;right:6px;"></div>
      <div class="bldg-win" style="top:52px;left:6px;"></div>
      <div class="bldg-win" style="top:52px;right:6px;"></div>
      <div class="bldg-win" style="top:72px;left:6px;"></div>
      <div class="bldg-win" style="top:72px;right:6px;"></div>
    </div>
    <div class="bldg" style="left:14px;width:34px;height:80px;">
      <div class="bldg-win" style="top:10px;left:5px;"></div>
      <div class="bldg-win" style="top:10px;right:5px;"></div>
      <div class="bldg-win" style="top:28px;left:5px;"></div>
      <div class="bldg-win" style="top:28px;right:5px;"></div>
      <div class="bldg-win" style="top:46px;left:5px;"></div>
      <div class="bldg-win" style="top:46px;right:5px;"></div>
    </div>
    <div class="ground"></div>
    <div class="hole"></div>
    <div class="num num-5">5</div>
    <div class="num num-0">0</div>
    <div class="num num-3">3</div>
    <div class="pole"></div>
    <div class="sign"><span>OOOPS</span></div>
    <div class="caution">
      <div class="caution-body">
        <div class="caution-stripe" style="top:0px;"></div>
        <div class="caution-stripe" style="top:12px;"></div>
        <div class="caution-stripe" style="top:24px;"></div>
        <div class="caution-stripe" style="top:36px;"></div>
        <div class="caution-stripe" style="top:48px;"></div>
      </div>
      <div class="caution-leg l"></div>
      <div class="caution-leg r"></div>
    </div>
  </div>

  <h1 class="maint-title">Maintenance</h1>
  <p class="maint-sub">Sorry, the page is temporarily unavailable.</p>
</div>
</body>
</html>