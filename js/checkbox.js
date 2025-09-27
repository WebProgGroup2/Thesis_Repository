// Sample arrays
const programs = ["BSCS", "BSP", "BSA", "BSCE", "MMA", "BSEE", "BSIT"];
const methodologies = ["Quantitative", "Qualitative", "Mixed Methodology", "Descriptive", "Experimental", "Correlational"];
const thesisTypes = ["Experimental", "Correlational", "Case Study", "Survey", "Action Research", "Ethnographic", "Phenomenological"];

/**
 * Generic function to render checkboxes
 * @param {string} listId - target <ul> id
 * @param {string[]} items - array of items
 * @param {number} visibleCount - how many to show before "More..."
 */
function renderCheckboxes(listId, items, visibleCount = 5) {
    const ul = document.getElementById(listId);

    // Render first batch (or all if <= visibleCount)
    items.slice(0, visibleCount).forEach(item => {
        ul.appendChild(makeCheckboxItem(item));
    });

    // If there are hidden items, add "More..." toggle
    if (items.length > visibleCount) {
        const moreBtn = document.createElement("li");
        moreBtn.className = "sidebar__list-item more-btn";
        moreBtn.textContent = "More...";
        ul.appendChild(moreBtn);

        // Toggle logic
        moreBtn.addEventListener("click", function () {
            if (this.dataset.expanded === "true") {
                ul.querySelectorAll(".extra-item").forEach(el => el.remove());
                this.textContent = "More...";
                this.dataset.expanded = "false";
            } else {
                items.slice(visibleCount).forEach(item => {
                    ul.insertBefore(makeCheckboxItem(item, true), moreBtn);
                });
                this.textContent = "Less...";
                this.dataset.expanded = "true";
            }
        });
    }
}

/**
 * Helper: Create a <li> with a checkbox
 */
function makeCheckboxItem(label, isExtra = false) {
    const li = document.createElement("li");
    li.className = "sidebar__list-item" + (isExtra ? " extra-item" : "");
    li.innerHTML = `<input type="checkbox"> ${label}`;
    return li;
}

// Render lists
renderCheckboxes("programsList", programs, 5);
renderCheckboxes("methodologyList", methodologies, 3); // show 3, rest hidden
renderCheckboxes("thesisList", thesisTypes, 5);
