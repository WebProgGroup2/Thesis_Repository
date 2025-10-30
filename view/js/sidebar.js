document.addEventListener("DOMContentLoaded", () => {
  const BASE_URL = `${window.location.origin}/thesis_repo/`;

  const contentWrapper = document.querySelector("body");
  if (!contentWrapper) return;


  const sideBar = document.createElement("div");
  sideBar.id = "side-bar";

  const titleWrapper = document.createElement("div");
  titleWrapper.classList.add("title-wrapper");
  titleWrapper.innerHTML = `
    <img src="${BASE_URL}view/assets/LRC Logo.png" alt="Logo" class="logo" />
    <div class="title-text">
      <span id="title">LRC THESIS</span>
      <span id="subtitle">REPOSITORY</span>
    </div>
  `;
  sideBar.appendChild(titleWrapper);

  
  titleWrapper.addEventListener("click", () => {
    window.location.href = `${BASE_URL}index.php`;
  });

  const categories = [
    {
      title: "PROGRAMS",
      icon: "school",
      items: [
        "MM",
        "BSAIS",
        "BSA",
        "BSBA-MA",
        "BSTM",
        "BSIT",
        "BSCS",
        "BSIS",
        "BSCrim",
        "BSESS",
        "BSPsy",
        "BMMA",
        "BACOMM",
        "BSArch",
        "BSCpE",
        "BSCE",
        "MIT",
        "MAEd-Eng",
        "MAEd-Fil",
        "MAEd-SPED",
        "MAEd-EM",
        "EdD-EM",
      ],
    },
    {
      title: "METHODOLOGY",
      icon: "science",
      items: [
        "Quantitative",
        "Qualitative",
        "Mixed-Methods",
        "Case Study",
        "Experimental Design",
        "Survey Method",
        "Grounded Theory",
        "Phenomenological",
      ],
    },
    {
      title: "THESIS_TYPE",
      icon: "type_specimen",
      items: [
        "Experimental Research",
        "Descriptive Research",
        "Correlational Research",
        "Qualitative Case Study",
        "Mixed-Methods Research",
        "Survey Research",
        "Action Research",
        "Ethnographic Research",
      ],
    },
  ];


  function renderCategory({ title, icon, items }) {
    const wrapper = document.createElement("div");
    wrapper.classList.add("category-wrapper");

    const titleWrapper = document.createElement("div");
    titleWrapper.classList.add("category-title-wrapper");
    titleWrapper.innerHTML = `
      <span class="material-symbols-outlined">${icon}</span>
      <span>${title}</span>
    `;
    wrapper.appendChild(titleWrapper);

  
    const carousel = document.createElement("div");
    carousel.classList.add("category-carousel");
    const visibleCount = 8;

    items.forEach((item, index) => {
      const label = document.createElement("label");
      label.innerHTML = `
        <input type="checkbox" name="${item}" value="${item}">
        ${item}
      `;
      if (index >= visibleCount) label.classList.add("hidden-checkbox");
      carousel.appendChild(label);
    });


    if (items.length > visibleCount) {
      const moreSpan = document.createElement("span");
      moreSpan.classList.add("show-more");
      moreSpan.textContent = "More...";
      carousel.appendChild(moreSpan);

      let expanded = false;
      moreSpan.addEventListener("click", () => {
        expanded = !expanded;
        carousel.querySelectorAll(".hidden-checkbox").forEach((el) => {
          el.style.display = expanded ? "inline-flex" : "none";
        });
        moreSpan.textContent = expanded ? "Show less" : "More...";
      });
    }

    wrapper.appendChild(carousel);
    return wrapper;
  }

  categories.forEach((cat) => {
    sideBar.appendChild(renderCategory(cat));
  });

  function getCookie(name) {
    const match = document.cookie.match(
      new RegExp("(^| )" + name + "=([^;]+)")
    );
    return match ? decodeURIComponent(match[2]) : null;
  }

  const role = getCookie("role"); // get user role from cookie

  if (role === "admin") {
    const thesisArchiveLink = document.createElement("div");
    thesisArchiveLink.classList.add("thesis-archive-wrapper");
    thesisArchiveLink.innerHTML = `
    <a href="${BASE_URL}view/pages/archive.php" class="thesis-archive-link">
      <span class="material-symbols-outlined">storage</span>
      <span id="archive-text">THESIS ARCHIVE</span>
    </a>
  `;
    sideBar.appendChild(thesisArchiveLink);
  }

 
  const socials = document.createElement("div");
  socials.classList.add("social-media");
  socials.innerHTML = `
    <span>FOLLOW US ON</span>
    <div class="icons">
      <div class="img-wrapper"><img src="${BASE_URL}view/assets/social-icons/yt.png" alt="youtube" /></div>
      <div class="img-wrapper"><img src="${BASE_URL}view/assets/social-icons/fb.png" alt="facebook" /></div>
      <div class="img-wrapper"><img src="${BASE_URL}view/assets/social-icons/insta.png" alt="instagram" /></div>
      <div class="img-wrapper"><img src="${BASE_URL}view/assets/social-icons/linked.png" alt="linkedin" /></div>
    </div>
  `;
  sideBar.appendChild(socials);


  contentWrapper.appendChild(sideBar);
  document.body.addEventListener("change", (e) => {
    if (e.target.matches("input[type='checkbox']")) {
      const selectedPrograms = [
        ...document.querySelectorAll(
          ".category-wrapper:nth-child(2) input:checked"
        ),
      ].map((cb) => cb.value);
      const selectedMethods = [
        ...document.querySelectorAll(
          ".category-wrapper:nth-child(3) input:checked"
        ),
      ].map((cb) => cb.value);
      const selectedTypes = [
        ...document.querySelectorAll(
          ".category-wrapper:nth-child(4) input:checked"
        ),
      ].map((cb) => cb.value);

      const event = new CustomEvent("filtersChanged", {
        detail: {
          programs: selectedPrograms,
          methods: selectedMethods,
          types: selectedTypes,
        },
      });
      document.dispatchEvent(event);
    }
  });
});
