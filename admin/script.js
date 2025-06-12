// assets/js/admin_scripts.js

document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("adminSidebar");
  const sidebarToggleMobile = document.getElementById("sidebarToggleMobile");
  const contentArea = document.getElementById("content-area");
  const tabLinks = document.querySelectorAll(".sidebar .tab-link");
  const pageTitleMobile = document.getElementById("pageTitleMobile");
  let initialLoad = true;

  // Function to create and manage sidebar overlay
  let sidebarOverlay = null;
  function manageOverlay() {
    if (!sidebarOverlay) {
      sidebarOverlay = document.createElement("div");
      sidebarOverlay.classList.add("sidebar-overlay");
      // Insert overlay after sidebar, or before main-content
      sidebar.parentNode.insertBefore(sidebarOverlay, sidebar.nextSibling);

      sidebarOverlay.addEventListener("click", function () {
        sidebar.classList.remove("active"); // Hide sidebar
        this.style.display = "none"; // Hide overlay
      });
    }
    return sidebarOverlay;
  }

  // Sidebar toggle for mobile
  if (sidebarToggleMobile && sidebar) {
    sidebarToggleMobile.addEventListener("click", function () {
      sidebar.classList.toggle("active");
      const overlay = manageOverlay();
      if (sidebar.classList.contains("active")) {
        overlay.style.display = "block";
      } else {
        overlay.style.display = "none";
      }
    });
  }

  // Function to load page content via AJAX
  function loadAdminPage(page, pushState = true) {
    // Show a loader (optional)
    contentArea.innerHTML =
      '<div class="d-flex justify-content-center align-items-center" style="min-height: 200px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    fetch(`pages/${page}.php`) // Assuming your content pages are in admin/pages/
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then((data) => {
        contentArea.innerHTML = data;
        if (pageTitleMobile) {
          // Find the active link's text content for the title
          const activeLink = document.querySelector(
            ".sidebar .tab-link.active"
          );
          pageTitleMobile.textContent = activeLink
            ? activeLink.textContent.trim()
            : "Admin";
        }

        // Update URL and browser history
        if (pushState) {
          const newUrl = window.location.pathname + `?page=${page}`;
          history.pushState({ page: page }, "", newUrl);
        }

        // Re-attach any dynamic event listeners if needed for the new content
        // Example: if (page === 'some_page_with_modal') { attachModalHandlers(); }
        // Or better, use event delegation for dynamically loaded content.
        if (
          page === "dashboard" &&
          typeof attachDashboardHandlers === "function"
        ) {
          // attachDashboardHandlers(); // From your original script
        }

        // Hide sidebar on mobile after a page is loaded
        if (
          window.innerWidth < 992 &&
          sidebar &&
          sidebar.classList.contains("active")
        ) {
          sidebar.classList.remove("active");
          const overlay = document.querySelector(".sidebar-overlay");
          if (overlay) overlay.style.display = "none";
        }
      })
      .catch((error) => {
        console.error("Error loading page:", error);
        contentArea.innerHTML = `<div class="alert alert-danger" role="alert">Failed to load content for ${page}. Please try again. Error: ${error.message}</div>`;
      });
  }

  // Handle tab link clicks
  tabLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      tabLinks.forEach((l) => l.classList.remove("active"));
      this.classList.add("active");

      const page = this.getAttribute("data-page");
      loadAdminPage(page);
      initialLoad = false;
    });
  });

  // Handle browser back/forward buttons
  window.addEventListener("popstate", function (event) {
    if (event.state && event.state.page) {
      tabLinks.forEach((l) => l.classList.remove("active"));
      const activeLink = document.querySelector(
        `.sidebar .tab-link[data-page="${event.state.page}"]`
      );
      if (activeLink) activeLink.classList.add("active");
      loadAdminPage(event.state.page, false); // false to not push state again
    } else if (initialLoad) {
      // If no state and it's the first load (e.g. direct URL with ?page=)
      // This handles the case where user directly navigates to admin_dashboard.php?page=somepage
      // The initial page load logic below handles this better.
    }
  });

  // Initial page load based on URL query parameter or default
  const urlParams = new URLSearchParams(window.location.search);
  const initialPage = urlParams.get("page") || "dashboard"; // Default to dashboard

  const activeLinkOnLoad = document.querySelector(
    `.sidebar .tab-link[data-page="${initialPage}"]`
  );
  if (activeLinkOnLoad) {
    tabLinks.forEach((l) => l.classList.remove("active"));
    activeLinkOnLoad.classList.add("active");
    loadAdminPage(initialPage, !urlParams.has("page")); // Don't push state if page was from URL query
  } else {
    // Fallback if the page in URL is invalid, load dashboard
    document
      .querySelector('.sidebar .tab-link[data-page="dashboard"]')
      .classList.add("active");
    loadAdminPage("dashboard", true);
  }
  initialLoad = false;
});
