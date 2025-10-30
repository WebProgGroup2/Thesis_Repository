document.addEventListener("DOMContentLoaded", () => {
  initRefreshButton();
  initThesisFetching();
  initSidebarFilters();
  initYearFilter();
  initPopupHandling();
  initImagePreview();
  initFileUpload();
  initAuthorField();
  initRichTextEditor();
  initMethodologySelect();
  initThesisTypeCheckboxes();
  initKeywordField();
  initReferenceField();
  initPageCountField();
  initProgramSelect();
  initSubmitHandler();
});

// ============================================================
// ============================================================
function initRefreshButton() {
  const refreshBtn = document.getElementById("refresh-btn");
  if (!refreshBtn) return;

  refreshBtn.addEventListener("click", (e) => {
    e.preventDefault();
    refreshBtn.disabled = true;
    refreshBtn.textContent = "Refreshing...";
    window.location.href = `${window.location.origin}/thesis_repo/view/pages/archive.php`;
  });
}

// ============================================================
// ============================================================
function initThesisFetching() {
  const thesisList = document.querySelector(".thesis-list");
  if (!thesisList) return;

  const SEARCH_URL = `${window.location.origin}/thesis_repo/controller/search.php`;
  const urlParams = new URLSearchParams(window.location.search);
  const query = urlParams.get("q") || "";
  const filter = urlParams.get("filter") || "all";

  window.fetchTheses = async ({
    query = "",
    programs = [],
    methods = [],
    types = [],
    filter = "all",
  } = {}) => {
    const params = new URLSearchParams({ ajax: 1, q: query });
    const filterMode =
      filter === "all" || filter === "advance" ? "none" : filter;

    if (programs.length) params.set("programs", programs.join(","));
    if (methods.length) params.set("methods", methods.join(","));
    if (types.length) params.set("types", types.join(","));
    params.set("filter", filterMode);

    thesisList.innerHTML = "<p>Loading results...</p>";

    try {
      const res = await fetch(`${SEARCH_URL}?${params.toString()}`);
      const resData = await res.json();
      thesisList.innerHTML = "";

      if (!resData.success) {
        thesisList.innerHTML = `<p style="color:red;">Error: ${
          resData.error || "Unknown issue"
        }</p>`;
        return;
      }

      const data = resData.data || [];
      if (data.length === 0) {
        thesisList.innerHTML = "<p>No matching theses found.</p>";
        return;
      }

      data.forEach((item) => thesisList.appendChild(createThesisRow(item)));
    } catch (err) {
      console.error("Fetch error:", err);
      thesisList.innerHTML = "<p>Error loading data.</p>";
    }
  };

  window.createThesisRow = (item) => {
    const row = document.createElement("div");
    row.className = "thesis-info-wrapper";
    row.dataset.thesisId = item.thesis_id;
    row.innerHTML = `
      <span class="thesis-info" id="title">${item.title || "N/A"}</span>
      <span class="thesis-info" id="author">${item.author || "N/A"}</span>
      <span class="thesis-info" id="pub-date">${item.pub_date || "N/A"}</span>
      <span class="thesis-info" id="pub-place">${item.pub_place || "N/A"}</span>
      <span class="thesis-info" id="methodology">${
        item.methodology || "N/A"
      }</span>
      <div class="btn-wrapper">
        <button class="interaction-btns view-btn"><span class="material-symbols-outlined">visibility</span></button>
        <button class="interaction-btns edit-btn"><span class="material-symbols-outlined">edit</span></button>
        <button class="interaction-btns delete-btn"><span class="material-symbols-outlined">delete</span></button>
      </div>
    `;
    row
      .querySelector(".view-btn")
      .addEventListener("click", () => openPopup(item.thesis_id, "view"));
    row
      .querySelector(".edit-btn")
      .addEventListener("click", () => openPopup(item.thesis_id, "edit"));
    row
      .querySelector(".delete-btn")
      .addEventListener("click", () => deleteThesis(item.thesis_id, "delete"));
    return row;
  };

  fetchTheses({ query, filter });
}

// ============================================================
// ============================================================
function initSidebarFilters() {
  function getSelectedValues(category) {
    const wrapper = [...document.querySelectorAll(".category-wrapper")].find(
      (el) =>
        el.querySelector(".category-title-wrapper span:last-child")
          ?.textContent === category
    );
    if (!wrapper) return [];
    return [...wrapper.querySelectorAll("input[type='checkbox']:checked")].map(
      (cb) => cb.value
    );
  }

  const sidebarObserver = new MutationObserver(() => {
    const checkboxes = document.querySelectorAll(
      "#side-bar input[type='checkbox']"
    );
    if (checkboxes.length === 0) return;

    checkboxes.forEach((cb) =>
      cb.addEventListener("change", () => {
        fetchTheses({
          query: new URLSearchParams(window.location.search).get("q") || "",
          filter:
            new URLSearchParams(window.location.search).get("filter") || "all",
          programs: getSelectedValues("PROGRAMS"),
          methods: getSelectedValues("METHODOLOGY"),
          types: getSelectedValues("THESIS_TYPE"),
        });
      })
    );

    sidebarObserver.disconnect();
  });

  sidebarObserver.observe(document.body, { childList: true, subtree: true });
}

// ============================================================
// ============================================================
function initYearFilter() {
  const yearFilter = document.getElementById("year-filter");
  if (!yearFilter) return;

  yearFilter.addEventListener("change", () => {
    const year = yearFilter.value;
    document.querySelectorAll(".thesis-info-wrapper").forEach((row) => {
      const pubDateText = row.querySelector("#pub-date")?.textContent.trim();
      const yearMatch = pubDateText?.match(/\b(19|20)\d{2}\b/);
      row.style.display =
        !year || (yearMatch && yearMatch[0] === year) ? "" : "none";
    });
  });
}

// ============================================================
// ============================================================
async function deleteThesis(thesis_id) {
  if (!confirm("Are you sure you want to delete this thesis?")) return;

  try {
    const response = await fetch(`${baseURL}controller/delete_thesis.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ thesis_id }),
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Server error (${response.status}): ${errorText}`);
    }

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      document.querySelector(`[data-thesis-id="${thesis_id}"]`)?.remove();
    } else {
      alert("❌ Delete failed: " + (result.error || "Unknown error"));
    }
  } catch (err) {
    console.error("Delete error:", err);
    alert("An unexpected error occurred while deleting the thesis.");
  }
}

// ============================================================
// ============================================================
function initPopupHandling() {
  const popUp = document.getElementById("pop-up");
  const thesisForm = document.getElementById("thesis-form");
  const addBtn = document.getElementById("add-btn");
  const submitBtn = document.getElementById("submit-thesis");
  const closeBtn = document.getElementById("close-popup");
  const purposePlaceholder = document.getElementById("purpose-label");
  window.purpose = "Add";


  window.resetForm = () => {
    console.log("🔄 Resetting form...");

 
    thesisForm.reset();


    window.authors = [];
    window.keywords = [];
    window.references = [];


    document.getElementById("author-list").innerHTML = "";
    document.getElementById("keyword-list").innerHTML = "";
    document.getElementById("reference-list").innerHTML = "";


    document.getElementById("img-preview").style.display = "none";
    document.getElementById("upload-text").style.display = "inline";
    document.getElementById("file-name").textContent = "Add thesis' PDF file";

    document.getElementById("cover-img").value = "";
    document.getElementById("thesis-file").value = "";

  
    document.getElementById("abstract-editor").innerHTML = "";


    document
      .querySelectorAll('input[name="thesisTypes"]')
      .forEach((checkbox) => {
        checkbox.checked = false;
      });


    document.querySelectorAll("select").forEach((select) => {
      select.selectedIndex = 0;
    });

  
    window.existingCover = null;
    window.existingPDF = null;

    console.log("✅ Form reset complete");
  };

  function setFormViewMode() {
    const form = document.getElementById("thesis-form");


    form
      .querySelectorAll(
        "input[type=text], input[type=number], input[type=date]"
      )
      .forEach((input) => {
        input.readOnly = true;
      });

    // File inputs
    form.querySelectorAll("input[type=file]").forEach((input) => {
      input.disabled = true;
    });

    // Select dropdowns
    form.querySelectorAll("select").forEach((select) => {
      select.disabled = true;
    });

    // Checkboxes
    form.querySelectorAll("input[type=checkbox]").forEach((cb) => {
      cb.disabled = true;
    });

    // Contenteditable elements
    form.querySelectorAll("[contenteditable]").forEach((div) => {
      div.contentEditable = "false";
    });

    // Hide submit button
    document.getElementById("submit-thesis").style.display = "none";
  }

  // Editable forms
  function setFormEditMode() {
    const form = document.getElementById("thesis-form");

    // Enable all inputs and selects
    form.querySelectorAll("input, select").forEach((el) => {
      el.readOnly = false;
      el.disabled = false;
    });

    // Contenteditable elements
    form.querySelectorAll("[contenteditable]").forEach((div) => {
      div.contentEditable = "true";
    });

    // Show submit button
    document.getElementById("submit-thesis").style.display = "block";
  }

  window.openPopup = (thesis_id = null, view_type = null) => {
    popUp.classList.add("active");

    if (thesis_id) {
      submitBtn.dataset.thesisId = thesis_id;
    } else {
      delete submitBtn.dataset.thesisId;
    }

    if (thesis_id && view_type) {
      submitBtn.style.display = view_type === "edit" ? "block" : "none";
      purpose = view_type.charAt(0).toUpperCase() + view_type.slice(1);
      retrieveThesisInfoForEdit(thesis_id);

      if (purpose === "View") {
        setFormViewMode();
      } else {
        setFormEditMode();
      }
    } else {
      resetForm();
      setFormEditMode();
      submitBtn.style.display = "block";
      purpose = "Add";
    }

    submitBtn.textContent = `${purpose} Thesis`;
    purposePlaceholder.textContent = `${purpose} Thesis`;
  };

  window.closePopup = () => {
    popUp.classList.remove("active");
    resetForm();
  };

  addBtn?.addEventListener("click", () => openPopup());
  closeBtn?.addEventListener("click", closePopup);
  thesisForm?.addEventListener("click", (e) => e.stopPropagation());
}

// ============================================================
// ============================================================
function initImagePreview() {
  const coverImgInput = document.getElementById("cover-img");
  const imgPreview = document.getElementById("img-preview");
  const uploadText = document.getElementById("upload-text");

  coverImgInput?.addEventListener("change", function () {
    const file = this.files[0];
    if (!file) {
      imgPreview.style.display = "none";
      uploadText.style.display = "inline";
      imgPreview.src = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      imgPreview.src = reader.result;
      imgPreview.style.display = "block";
      uploadText.style.display = "none";
    };
    reader.readAsDataURL(file);
  });
}

// ============================================================
// ============================================================
function initFileUpload() {
  const fileInput = document.getElementById("thesis-file");
  const addFileBtn = document.getElementById("add-file-btn");
  const fileNameSpan = document.getElementById("file-name");

  addFileBtn?.addEventListener("click", (e) => {
    e.preventDefault();
    fileInput?.click();
  });

  fileInput?.addEventListener("change", (e) => {
    const file = e.target.files[0];
    fileNameSpan.textContent = file
      ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`
      : "No file selected";
  });
}

// ============================================================
// ============================================================
function initAuthorField() {
  const authorFirstname = document.getElementById("author-fName");
  const authorMiddlename = document.getElementById("author-mName");
  const authorLastname = document.getElementById("author-lName");
  const authorNameFields = document.querySelectorAll(".author-field");
  const authorList = document.getElementById("author-list");
  window.authors = [];

  window.addAuthor = function (name) {
    const wrapper = document.createElement("div");
    wrapper.className = "input-result-wrapper";
    wrapper.innerHTML = `
      <button type="button" class="delete-btn"><span class="material-symbols-outlined">delete</span></button>
      <span class="author-name">${
        name.firstname + " " + name.middlename + " " + name.lastname
      }</span>
    `;
    wrapper.querySelector(".delete-btn").addEventListener("click", () => {
      wrapper.remove();
      window.authors = window.authors.filter((a) => a !== name);
    });
    authorList.appendChild(wrapper);
    window.authors.push(name);
  };

  authorLastname?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const fnameCheck = authorFirstname && authorFirstname.value.length > 0;
      const lnameCheck = authorLastname && authorLastname.value.length > 0;

      if (fnameCheck && lnameCheck) {
        const name = {
          firstname: authorFirstname.value.trim(),
          middlename:
            authorMiddlename.value.length > 0
              ? authorMiddlename.value.trim()
              : "",
          lastname: authorLastname.value.trim(),
        };

        if (name) window.addAuthor(name);

        authorNameFields.forEach((field) => {
          field.value = "";
        });
      } else {
        alert("Please provide a firstname");
      }
    }
  });
}

// ============================================================
// ============================================================
function initRichTextEditor() {
  const editor = document.getElementById("abstract-editor");
  const toolbarButtons = document.querySelectorAll(".editor-toolbar button");

  function toggleFormatting(command) {
    document.execCommand(command, false, null);
  }

  function updateButtonStates() {
    toolbarButtons.forEach((btn) => {
      const command = btn.dataset.command;
      btn.classList.toggle("active", document.queryCommandState(command));
    });
  }

  toolbarButtons.forEach((btn) =>
    btn.addEventListener("click", () => {
      toggleFormatting(btn.dataset.command);
      editor.focus();
      updateButtonStates();
    })
  );

  editor?.addEventListener("mouseup", updateButtonStates);
  editor?.addEventListener("keyup", updateButtonStates);
}

// ============================================================
// Methodology Select
// ============================================================
function initMethodologySelect() {
  const methodologySelect = document.getElementById("methodology-selection");
  const methodologies = [
    { id: 1, name: "Quantitative" },
    { id: 2, name: "Qualitative" },
    { id: 3, name: "Mixed-Methods" },
    { id: 4, name: "Case Study" },
    { id: 5, name: "Experimental Design" },
    { id: 6, name: "Survey Method" },
    { id: 7, name: "Grounded Theory" },
    { id: 8, name: "Phenomenological" },
  ];

  methodologies.forEach(({ id, name }) => {
    const option = document.createElement("option");
    option.value = id;
    option.textContent = name;
    methodologySelect.appendChild(option);
  });
}

// ============================================================
// ============================================================
function initThesisTypeCheckboxes() {
  const container = document.getElementById("type-checkboxes");
  const thesisTypes = [
    { id: 1, name: "Experimental Research" },
    { id: 2, name: "Descriptive Research" },
    { id: 3, name: "Correlational Research" },
    { id: 4, name: "Qualitative Case Study" },
    { id: 5, name: "Mixed-Methods Research" },
    { id: 6, name: "Survey Research" },
    { id: 7, name: "Action Research" },
    { id: 8, name: "Ethnographic Research" },
  ];

  thesisTypes.forEach(({ id, name }) => {
    const wrapper = document.createElement("div");
    wrapper.classList.add("checkbox-item");
    const input = document.createElement("input");
    input.type = "checkbox";
    input.id = `type-${id}`;
    input.value = id;
    input.name = "thesisTypes";
    const label = document.createElement("label");
    label.htmlFor = input.id;
    label.textContent = name;
    wrapper.appendChild(input);
    wrapper.appendChild(label);
    container.appendChild(wrapper);
  });
}

// ============================================================
// ============================================================
function initKeywordField() {
  const keywordInput = document.getElementById("keyword-textfield");
  const keywordList = document.getElementById("keyword-list");
  window.keywords = [];

  window.addKeyword = function (word) {
    const wrapper = document.createElement("div");
    wrapper.className = "input-result-wrapper";
    wrapper.innerHTML = `
      <button type="button" class="delete-btn"><span class="material-symbols-outlined">delete</span></button>
      <span class="keyword-name">${word}</span>
    `;
    wrapper.querySelector(".delete-btn").addEventListener("click", () => {
      wrapper.remove();
      window.keywords = window.keywords.filter((k) => k !== word);
    });
    keywordList.appendChild(wrapper);
    window.keywords.push(word);
  };

  keywordInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const word = keywordInput.value.trim();
      if (word && !window.keywords.includes(word)) window.addKeyword(word);
      keywordInput.value = "";
    }
  });
}

// ============================================================
// ============================================================
function initReferenceField() {
  const referenceInput = document.getElementById("reference-textfield");
  const referenceList = document.getElementById("reference-list");
  window.references = [];

  window.addReference = function (ref) {
    const wrapper = document.createElement("div");
    wrapper.className = "input-result-wrapper";
    wrapper.innerHTML = `
      <button type="button" class="delete-btn"><span class="material-symbols-outlined">delete</span></button>
      <span class="reference-text">${ref}</span>
    `;
    wrapper.querySelector(".delete-btn").addEventListener("click", () => {
      wrapper.remove();
      window.references = window.references.filter((r) => r !== ref);
    });
    referenceList.appendChild(wrapper);
    window.references.push(ref);
  };

  referenceInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const ref = referenceInput.value.trim();
      if (ref) window.addReference(ref);
      referenceInput.value = "";
    }
  });
}

// ============================================================
// ============================================================
function initPageCountField() {
  const pageCountInput = document.getElementById("pagecount-textfield");
  pageCountInput?.addEventListener("input", () => {
    let value = parseInt(pageCountInput.value, 10);
    if (value < 1 || isNaN(value)) pageCountInput.value = "";
  });
}

// ============================================================
// ============================================================
function initProgramSelect() {
  const programSelect = document.getElementById("program-selection");
  const programs = [
    { id: 1, name: "Master in Management", code: "MM" },
    { id: 2, name: "BS Accounting Information System", code: "BSAIS" },
    { id: 3, name: "BS Accountancy", code: "BSA" },
    {
      id: 4,
      name: "BS Business Administration major in Marketing and Advertising",
      code: "BSBA-MA",
    },
    { id: 5, name: "BS Tourism Management", code: "BSTM" },
    { id: 6, name: "BS Information Technology", code: "BSIT" },
    { id: 7, name: "BS Computer Science", code: "BSCS" },
    { id: 8, name: "BS Information Systems", code: "BSIS" },
    { id: 9, name: "BS Criminology", code: "BSCrim" },
    {
      id: 10,
      name: "Bachelor of Science in Exercise and Sports Science",
      code: "BSESS",
    },
    { id: 11, name: "BS Psychology", code: "BSPsy" },
    { id: 12, name: "Bachelor of Multimedia Arts", code: "BMMA" },
    { id: 13, name: "BA Communication", code: "BACOMM" },
    { id: 14, name: "BS Architecture", code: "BSArch" },
    { id: 15, name: "BS Computer Engineering", code: "BSCpE" },
    { id: 16, name: "BS Civil Engineering", code: "BSCE" },
    { id: 17, name: "Master in Information Technology", code: "MIT" },
    { id: 18, name: "MA in Education, Major in English", code: "MAEd-Eng" },
    { id: 19, name: "MA in Education, Major in Filipino", code: "MAEd-Fil" },
    {
      id: 20,
      name: "MA in Education, Major in Special Education",
      code: "MAEd-SPED",
    },
    {
      id: 21,
      name: "MA in Education, Major in Educational Management",
      code: "MAEd-EM",
    },
    {
      id: 22,
      name: "Doctor of Education, Major in Educational Management",
      code: "EdD-EM",
    },
  ];

  programs.forEach(({ id, name, code }) => {
    const option = document.createElement("option");
    option.value = id;
    option.textContent = `${name} (${code})`;
    programSelect.appendChild(option);
  });
}

// ============================================================
// ============================================================

async function uploadFiles(coverFile, pdfFile) {
  const formData = new FormData();
  formData.append("thesis-cover", coverFile);
  formData.append("thesis-file", pdfFile);

  try {
    const response = await fetch(baseURL + "controller/upload_file.php", {
      method: "POST",
      body: formData,
    });


    const result = await response.json();

    if (result.cover || result.pdf) {
      return result; 
    } else {
      console.error("Upload failed:", result);
      return null;
    }
  } catch (err) {
    console.error("Upload error:", err);
    return null;
  }
}

// ============================================================
// ============================================================
function initSubmitHandler() {
  const submitBtn = document.getElementById("submit-thesis");

  submitBtn?.addEventListener("click", handleThesisSubmission);
}

async function handleThesisSubmission(e) {
  e.preventDefault();

  try {
   
    if (purpose === "Add" && !validateThesisForm()) {
      return; 
    }

    const thesisData = await prepareThesisData();

    if (!thesisData) {
      return; 
    }

    const result = await saveThesis(thesisData);

    if (result.success) {
      showSuccess(result.message || getSuccessMessage(purpose));
      closePopup();

      if (purpose === "Edit") {
        initThesisFetching(); 
      }
    } else {
      throw new Error(result.error || "Operation failed");
    }
  } catch (error) {
    console.error("Thesis submission error:", error);
    showError(error.message || "An unexpected error occurred");
  }
}

// ============================================================
// ============================================================
async function prepareThesisData() {
  const baseData = getBaseFormData();

  // Handle file uploads for both Add and Edit
  if (purpose === "Add" || hasFileChanges()) {
    const fileResult = await handleFileUploads();

    if (!fileResult) {
      showError("File upload failed. Please try again.");
      return null;
    }

    return {
      ...baseData,
      cover_path: fileResult.cover,
      pdf_path: fileResult.pdf,
    };
  }


  if (purpose === "Edit" && !(await confirmNoFileChanges())) {
    return null; 
  }

  return {
    ...baseData,
    cover_path: window.existingCover || null,
    pdf_path: window.existingPDF || null,
  };
}

function getBaseFormData() {
  const commonData = {
    title: getValue("#title-textfield"),
    authors: window.authors || [],
    pub_date: getValue(".date-picker"),
    pub_place: getValue("#place-textfield"),
    methodology_id: getValue("#methodology-selection"),
    thesis_types: getCheckedValues('input[name="thesisTypes"]:checked'),
    keywords: window.keywords || [],
    references: window.references || [],
    page_count: parseInt(getValue("#pagecount-textfield")) || 0,
    program_id: getValue("#program-selection"),
    abstract: getHTML("#abstract-editor"),
  };

  if (purpose === "Edit") {
    const thesisId = document.getElementById("submit-thesis")?.dataset.thesisId;
    if (!thesisId) {
      throw new Error("Missing thesis ID. Cannot update.");
    }
    commonData.thesis_id = thesisId;
  }

  return commonData;
}

// ============================================================
// ============================================================
function hasFileChanges() {
  const coverFile = document.getElementById("cover-img").files[0];
  const pdfFile = document.getElementById("thesis-file").files[0];
  return !!(coverFile || pdfFile);
}

async function handleFileUploads() {
  const coverFile = document.getElementById("cover-img").files[0];
  const pdfFile = document.getElementById("thesis-file").files[0];

  // For Add mode, both files are required
  if (purpose === "Add" && (!coverFile || !pdfFile)) {
    showError("Both cover image and PDF file are required for new thesis.");
    return null;
  }

  return await uploadFiles(coverFile, pdfFile);
}

async function confirmNoFileChanges() {
  return confirm("No changes in files. Do you want to continue saving?");
}

// ============================================================
// ============================================================
async function saveThesis(data) {
  const endpoint =
    purpose === "Add"
      ? "controller/save_thesis.php"
      : "controller/edit_thesis.php";

  console.log("📦 Sending data to server:", data); 

  const response = await fetch(`${baseURL}${endpoint}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });

  console.log("📡 Response status:", response.status); 

  if (!response.ok) {

    const errorText = await response.text();
    console.error("Server error response:", errorText);
    throw new Error(`Server error (${response.status}): ${errorText}`);
  }

  return await response.json();
}

// ============================================================
// ============================================================
function getValue(selector) {
  const element = document.querySelector(selector);
  return element?.value?.trim() || "";
}

function getHTML(selector) {
  const element = document.querySelector(selector);
  return element?.innerHTML || "";
}

function getCheckedValues(selector) {
  return Array.from(document.querySelectorAll(selector)).map((el) =>
    Number(el.value)
  );
}

function getSuccessMessage(action) {
  const messages = {
    Add: "Thesis uploaded successfully!",
    Edit: "Thesis updated successfully!",
  };
  return messages[action] || "Operation completed successfully!";
}

function showSuccess(message) {
  alert(` ${message}`);
}

function showError(message) {
  alert(` ${message}`);
}

// ================================
// ================================
function validateThesisForm() {

  const title = document.getElementById("title-textfield").value.trim();
  const authorsCount = authors.length; 
  const coverFile = document.getElementById("cover-img").files[0];
  const pdfFile = document.getElementById("thesis-file").files[0];
  const abstract = document.getElementById("abstract-editor").innerText.trim();
  const methodology = document.getElementById("methodology-selection").value;
  const thesisTypes = document.querySelectorAll(
    'input[name="thesisTypes"]:checked'
  );
  const keywordsCount = keywords.length; 
  const referencesCount = references.length; 
  const pageCount = document.getElementById("pagecount-textfield").value;
  const program = document.getElementById("program-selection").value;

  
  if (!title) return alert("Please enter a title.");
  if (!coverFile) return alert("Please upload a thesis cover image.");
  if (!pdfFile) return alert("Please upload the thesis PDF.");
  if (!authorsCount) return alert("Please add at least one author.");
  if (!abstract) return alert("Please enter the abstract.");
  if (!methodology) return alert("Please select a methodology.");
  if (thesisTypes.length === 0)
    return alert("Please select at least one thesis type.");
  if (!keywordsCount) return alert("Please add at least one keyword.");
  if (!referencesCount) return alert("Please add at least one reference.");
  if (!pageCount || pageCount < 1)
    return alert("Please enter a valid page count.");
  if (!program) return alert("Please select a program.");

 
  return true;
}

// ================================
// ================================
async function retrieveThesisInfoForEdit(thesis_id) {
  try {
    const res = await fetch(
      `/thesis_repo/controller/get_thesis.php?id=${thesis_id}`
    );
    const data = await res.json();

    if (data.error) {
      alert("Error fetching thesis data: " + data.error);
      return;
    }

    // Populate title
    document.getElementById("title-textfield").value = data.title || "";

    //Populate cover image preview
    const coverImg = document.getElementById("img-preview");
    const uploadText = document.getElementById("upload-text");
    if (data.thesis_cover) {
      coverImg.src = baseURL + data.thesis_cover;
      coverImg.style.display = "block";
      uploadText.style.display = "none";
    } else {
      coverImg.src = "";
      coverImg.style.display = "none";
      uploadText.style.display = "inline";
    }

    // Populate PDF file name (you can't set file input for security)
    document.getElementById("file-name").textContent = data.file_location
      ? data.file_location
      : "Add thesis' PDF file";

    // Populate authors
    authors = []; // reset current authors array

    console.log(authors)
    const authorList = document.getElementById("author-list");
    authorList.innerHTML = "";
    data.authors.forEach((authorName) => addAuthor(authorName));

    // Populate the calendar
    document.querySelector(".date-picker").value = data.pub_date || "";

    // Populate the publication place
    document.getElementById("place-textfield").value = data.pub_place || "";

    //Populate abstract
    document.getElementById("abstract-editor").innerHTML = data.abstract || "";

    // Populate methodology selection
    document.getElementById("methodology-selection").value =
      data.method_id || "";

    //Populate thesis types checkboxes
    const selectedTypes = data.thesis_types_id || []; // assuming array of type IDs
    document.querySelectorAll('input[name="thesisTypes"]').forEach((cb) => {
      cb.checked = selectedTypes.includes(Number(cb.value));
    });

    //Populate keywords
    keywords = [];
    const keywordList = document.getElementById("keyword-list");
    keywordList.innerHTML = "";
    data.keywords.forEach((kw) => addKeyword(kw));

    // Populate references
    references = [];
    const referenceList = document.getElementById("reference-list");
    referenceList.innerHTML = "";
    data.references_list.forEach((ref) => addReference(ref));

    // Populate page count
    document.getElementById("pagecount-textfield").value =
      data.page_count || "";

    //Populate program
    document.getElementById("program-selection").value = data.program_id || "";
  } catch (err) {
    console.error("Error fetching thesis:", err);
    alert("Failed to retrieve thesis data for editing.");
  }
}
