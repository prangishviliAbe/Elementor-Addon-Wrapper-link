(function () {
    'use strict';

    /**
     * Apply cursor style to all wrapper-link-enabled elements currently in the DOM.
     * Runs on initial load and whenever the DOM mutates (e.g. AJAX filter results).
     */
    function applyWrapperLinkStyles() {
        document.querySelectorAll('.wrapper-link-enabled').forEach(function (el) {
            if (el.dataset.wrapperLinkUrl && !el.dataset.ewlStyled) {
                el.style.cursor = 'pointer';
                el.dataset.ewlStyled = '1';
            }
        });
    }

    /**
     * Event-delegated click handler.
     * Works for elements present at page load AND elements injected later
     * by AJAX (JetSmartFilters, FacetWP, JetEngine, etc.).
     */
    document.addEventListener('click', function (e) {
        // Find the closest wrapper-link-enabled ancestor of the click target
        var wrapper = e.target.closest('.wrapper-link-enabled');
        if (!wrapper) return;

        // Don't hijack clicks on interactive children
        if (e.target.closest('a, button, input, textarea, select, label, [role="button"]')) return;

        var url = wrapper.dataset.wrapperLinkUrl;
        if (!url) return;

        var isExternal = wrapper.dataset.wrapperLinkExternal === 'true';
        var nofollow   = wrapper.dataset.wrapperLinkNofollow === 'true';

        try {
            var a = document.createElement('a');
            a.href = url;
            if (isExternal) a.target = '_blank';
            if (nofollow)   a.rel    = 'nofollow';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            a.remove();
        } catch (err) {
            console.error('Wrapper Link error', err);
        }
    });

    // --- Initial run ---
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyWrapperLinkStyles);
    } else {
        applyWrapperLinkStyles();
    }

    // --- MutationObserver: watch for dynamically added elements ---
    var observer = new MutationObserver(function (mutations) {
        var shouldScan = false;
        for (var i = 0; i < mutations.length; i++) {
            if (mutations[i].addedNodes.length) {
                shouldScan = true;
                break;
            }
        }
        if (shouldScan) {
            applyWrapperLinkStyles();
        }
    });

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });

    // --- JetSmartFilters specific AJAX hooks ---
    // JetSmartFilters dispatches custom events and uses jQuery triggers after AJAX content loads
    document.addEventListener('jet-filter-content-rendered', applyWrapperLinkStyles);
    document.addEventListener('jet-engine-listing-loaded',   applyWrapperLinkStyles);

    // jQuery-based hooks (JetSmartFilters often relies on jQuery events)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('ajaxComplete', applyWrapperLinkStyles);
        jQuery(document).on('jet-filter-content-rendered', applyWrapperLinkStyles);
    }
})();
