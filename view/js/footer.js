// footer.js

// Get the base URL from a global PHP variable (passed via script tag)

const main = document.querySelector(".main");
if (!main) {
  console.warn('Main element with class "main" not found.');
} else {
  const footer = document.createElement("div");
  footer.classList.add("main-sections");
  footer.id = "footer";

  footer.innerHTML = `
    <div id="upper-section" class="footer-section">
      <div id="left-side">
        <span class="lrc-info">NU LAGUNA LRC</span>
        <span class="lrc-info">
          <span class="material-symbols-outlined">nest_clock_farsight_analog</span>
          Open Time: 8:00 A.M. - 8:00 P.M.
        </span>
        <span class="lrc-info">
          <span class="material-symbols-outlined">mail</span>
          nulagunalrc@gmail.com
        </span>
        <span class="lrc-info">
          <span class="material-symbols-outlined">call</span>
          09999999999
        </span>
      </div>
      <div id="right-side">
        <div id="nu-logo-wrapper" class="logo-wrapper">
          <img src="${baseURL}view/assets/nu-logo.png" alt="NU Logo">
        </div>
        <div id="anniversary-wrapper" class="logo-wrapper">
          <img src="${baseURL}view/assets/125 anniversary 1.png" alt="125th Anniversary Logo">
        </div>
        <div id="nu-slogan">
          <div id="school-name">
            <span>NATIONAL UNIVERSITY</span>
          </div>
          <div id="school-motto">
            <span id="first-motto">EDUCATION THAT WORKS</span>
            <div id="line"></div>
            <span>FOUNDED FOR FILIPINOS, BUILT FOR THE NATION</span>
          </div>
        </div>
      </div>
    </div>
    <div id="lower-section" class="footer-section">
      <a href="#"><span>Contact</span></a>
      <a href="#"><span>Terms & Condition</span></a>
      <a href="#"><span>Privacy Policy</span></a>
    </div>
  `;

  main.appendChild(footer);
}
