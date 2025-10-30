
function getCookie(name) {
  const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  return match ? decodeURIComponent(match[2]) : null;
}
const header = document.getElementById("header");

if (header) {
  const accountWrapper = document.createElement("div");
  accountWrapper.classList.add("account-wrapper");

  const accountLogo = document.createElement("span");
  accountLogo.classList.add("material-symbols-outlined");
  accountLogo.id = "account-logo";
  accountLogo.textContent = "account_circle";

  const accountOwner = document.createElement("span");
  accountOwner.id = "account-owner";

  const firstName = getCookie("first_name");
  const role = getCookie("role");

  if (firstName && role) {
    accountOwner.textContent = `${firstName.toUpperCase()}`;
  } else {
    accountOwner.textContent = "Guest";
  }

  const optionWrapper = document.createElement("div");
  optionWrapper.classList.add("option-wrapper");
  optionWrapper.style.display = "none";

  if (!firstName) {
    // Not logged in
    const signInOption = document.createElement("span");
    signInOption.classList.add("option-text");
    signInOption.textContent = "Sign in";
    signInOption.addEventListener("click", () => {
      window.location.href = baseURL + "view/pages/sign-in.php";
    });

    const createAccountOption = document.createElement("span");
    createAccountOption.classList.add("option-text");
    createAccountOption.textContent = "Create Account";
    createAccountOption.addEventListener("click", () => {
      window.location.href = baseURL + "view/pages/create-account.php";
    });

    optionWrapper.appendChild(signInOption);
    optionWrapper.appendChild(createAccountOption);
  } else {
    // Logged in
    const logoutOption = document.createElement("span");
    logoutOption.classList.add("option-text");
    logoutOption.textContent = "Sign out";
    logoutOption.addEventListener("click", () => {
      // Redirect to PHP logout handler
      window.location.href = baseURL + "controller/logout.php";
    });

    optionWrapper.appendChild(logoutOption);
  }

  accountWrapper.appendChild(accountLogo);
  accountWrapper.appendChild(accountOwner);
  accountWrapper.appendChild(optionWrapper);
  header.appendChild(accountWrapper);

  let visible = false;
  accountOwner.addEventListener("click", () => {
    visible = !visible;
    optionWrapper.style.display = visible ? "flex" : "none";
  });

  document.addEventListener("click", (e) => {
    if (
      visible &&
      !accountOwner.contains(e.target) &&
      !optionWrapper.contains(e.target)
    ) {
      visible = false;
      optionWrapper.style.display = "none";
    }
  });
}
