document.addEventListener("DOMContentLoaded", () => {
  const formWrapper = document.getElementById("form-wrapper");

  // Add a small delay for a smoother effect (optional)
  setTimeout(() => {
    formWrapper.classList.add("show");
  }, 100);

  document.getElementById("sign-in-btn").addEventListener("click", () => {
    window.location.href = baseURL + "view/pages/sign-in.php";
  });

  document.getElementById("back-btn").addEventListener("click", () => {
    window.location.href = baseURL + "index.php";
  });
  document
    .getElementById("form-wrapper")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      // Get form values
      const formData = {
        schoolId: document.getElementById("school-id").value,
        email: document.getElementById("email").value,
        firstname: document.getElementById("firstname").value,
        lastname: document.getElementById("lastname").value,
        password: document.getElementById("password").value,
        role: document.getElementById("role-selection").value,
      };

      // Validate role is selected

      if (!formData.role || formData.role === "" || formData.role === null) {
        alert("Please select a role");
        document.getElementById("role-selection").focus(); // Focus on the select
        return false;
      }

      // Send POST request
      fetch(baseURL + "controller/process-account.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Account created successfully!");
            window.location.href = baseURL + "view/pages/sign-in.php";
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred. Please try again.");
        });
    });
});
