
<?php if ($has_remote_cdn == 1) { ?>
    <!-- JQuery-->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/jquery.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>

    <!-- jQuery UI 1.11.4 -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/jquery-ui.min.js"></script>

    <!-- Bootstrap 3.3.7 -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/bootstrap.min.js"></script>

    <!-- datepicker -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/bootstrap-datepicker.js"></script>

    <!-- adminlte -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/adminlte.min.js"></script>

    <!-- jquery.dataTables -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/jquery.dataTables.min.js"></script>

    <!-- dataTables.bootstrap -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/dataTables.bootstrap.min.js"></script>

    <!-- dataTables.buttons -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/dataTables.buttons.min.js"></script>

    <!-- pdfmake -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/pdfmake.min.js"></script>

    <!-- jszip -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/jszip.min.js"></script>

    <!-- vfs_fonts -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/vfs_fonts.js"></script>

    <!-- buttons.html5 -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/buttons.html5.min.js"></script>

    <!-- buttons.print -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/buttons.print.min.js"></script>

    <!-- jquery.form -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/jquery.form.js"></script>

    <!-- select2 -->
    <script src="https://7dbeefd5.sl-3mg.pages.dev/select2.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>

<?php } else { ?>
    <!-- JQuery-->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>

    <!-- jQuery UI 1.11.4 -->
    <script src="bower_components/jquery-ui/jquery-ui.min.js"></script>

    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- datepicker -->
    <script src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

    <!-- adminlte -->
    <script src="dist/js/adminlte.min.js"></script>

    <!-- jquery.dataTables -->
    <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>

    <!-- dataTables.bootstrap -->
    <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <!-- dataTables.buttons -->
    <script src="bower_components/datatables.net/js/dataTables.buttons.min.js"></script>

    <!-- pdfmake -->
    <script src="bower_components/datatables.net/js/pdfmake.min.js"></script>

    <!-- jszip -->
    <script src="bower_components/datatables.net/js/jszip.min.js"></script>

    <!-- vfs_fonts -->
    <script src="bower_components/datatables.net/js/vfs_fonts.js"></script>

    <!-- buttons.html5 -->
    <script src="bower_components/datatables.net/js/buttons.html5.min.js"></script>

    <!-- buttons.print -->
    <script src="bower_components/datatables.net/js/buttons.print.min.js"></script>

    <!-- jquery.form -->
    <script src="scripts/jquery.form.js"></script>

    <!-- select2 -->
    <script src="bower_components/select2/select2.min.js"></script>

<?php } ?>


<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>




<script src="scripts/common.js?v=1443"></script>
<script src="scripts/main.js?v=2025-May-06-11-17"></script>



<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- select2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {

        const popupNotifElement = document.getElementById('popup_notif');
        const notifCountElement = document.getElementById('notif_count');
        let topHighlightsElement = null;

        let popupNotifInterval = null;
        let notifCountInterval = null;
        let topHighlightsInterval = null;

        // starts top highlights interval
        function startTopHighlightsInterval() {
            if (!topHighlightsInterval && topHighlightsElement) {
                topHighlightsInterval = setInterval(function () {
                    top_highlights_summary(); // Call specific to this element
                }, 60000); // 1 minute interval
            }
        }

        // stops top highlights interval
        function stopTopHighlightsInterval() {
            if (topHighlightsInterval) {
                clearInterval(topHighlightsInterval);
                topHighlightsInterval = null;
            }
        }

        // starts popup notifications interval
        function startPopupNotificationsInterval() {
            if (!popupNotifInterval && popupNotifElement) {
                popupNotifInterval = setInterval(function () {
                    notifications_display(); // Call specific to this element
                }, 60000); // 1 minute interval
            }
        }

        // stops popup notifications interval
        function stopPopupNotificationsInterval() {
            if (popupNotifInterval) {
                clearInterval(popupNotifInterval);
                popupNotifInterval = null;
            }
        }

        // starts notifications count interval
        function startNotifCountInterval() {
            if (!notifCountInterval && notifCountElement) {
                notifCountInterval = setInterval(function () {
                    notifications_count(); // Call specific to this element
                }, 60000); // 1 minute interval
            }
        }

        // stops notifications count interval
        function stopNotifCountInterval() {
            if (notifCountInterval) {
                clearInterval(notifCountInterval);
                notifCountInterval = null;
            }
        }

        // IntersectionObserver callback
        function observerCallback(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log(`${entry.target.id} is in the viewport, starting interval`);
                    if (entry.target === popupNotifElement) {
                        startPopupNotificationsInterval();
                    } else if (entry.target === notifCountElement) {
                        startNotifCountInterval();
                    } else if (entry.target === topHighlightsElement) {
                        startTopHighlightsInterval();
                    }
                } else {
                    console.log(`${entry.target.id} is out of the viewport, stopping interval`);
                    if (entry.target === popupNotifElement) {
                        stopPopupNotificationsInterval();
                    } else if (entry.target === notifCountElement) {
                        stopNotifCountInterval();
                    } else if (entry.target === topHighlightsElement) {
                        stopTopHighlightsInterval();
                    }
                }
            });
        }

        // Create IntersectionObserver instance
        const observer = new IntersectionObserver(observerCallback, {
            root: null, // Use the viewport as the root
            threshold: 0.1 // Element is considered in view when 10% of it is visible
        });

        // Start observing popup, notifCount & topHighlights elements if they exist
        if (popupNotifElement) observer.observe(popupNotifElement);
        if (notifCountElement) observer.observe(notifCountElement);
        // top-highlights element not immediately available, so observe after 10 seconds
        setTimeout(() => {
            topHighlightsElement = document.getElementById('top-highlights');
            if (topHighlightsElement) {
                observer.observe(topHighlightsElement);
            }
        }, 10000);

        const currentUrl = window.location.href;
        const urlObj = new URL(currentUrl);
        const currentPath = urlObj.pathname;
        const queryString = urlObj.search;
        const hasQueryString = queryString !== '';


        // !currentPath.includes('/index')
        const pathExcludes = ['/index', '/scoring'];
        if ($('select')?.length > 0 && !hasQueryString && !pathExcludes.some(path => currentPath.includes(path))) {
            $('select').select2();
        }

        convert_to_hyperlinks();
        ////-----For hyperlink
        /* setTimeout(function() {
             showCallout('.superlnk_', 'You can now preview customer account [x]', 'calloutDismissed1');
         }, 2000); */

        ////-----For navigation
        /*  setTimeout(function() {
              showCallout('#nav_direct', 'You can navigate between loans directly without going to main menu [x]', 'calloutDismissed2');
          }, 2000);
  
          ////-----Universal search
          setTimeout(function() {
              showCallout('.search_bar', 'You can now search for loans, customers, payments e.t.c [x]', 'calloutDismissed3');
          }, 2000); */

    });








    $(function () {
        $('.btn').dblclick(false);
    });

    $('document').ready(function () {
        //===== Begin Dark Mode toggle script
        const isDarkMode = localStorage.getItem("slDarkMode") === "true";
        const darkModeToggle = document.getElementById("slDarkModeToggle");

        if (darkModeToggle) {
            // === Set correct icon and title on page load
            darkModeToggle.textContent = isDarkMode ? "☀️" : "🌙";
            darkModeToggle.setAttribute("title", isDarkMode ? "Click to Switch to Light Mode" : "Click to Switch to Dark Mode");

            darkModeToggle.addEventListener("click", () => {
                const isEnabled = DarkReader.isEnabled();

                if (isEnabled) {
                    DarkReader.disable();
                    localStorage.setItem("slDarkMode", "false");
                    darkModeToggle.textContent = "🌙"; // Moon icon for dark mode off
                    darkModeToggle.setAttribute("title", "Click to Switch to Dark Mode");
                } else {
                    DarkReader.enable({
                        brightness: 100,
                        contrast: 90,
                        sepia: 10
                    });
                    localStorage.setItem("slDarkMode", "true");
                    darkModeToggle.textContent = "☀️"; // Sun icon for dark mode on
                    darkModeToggle.setAttribute("title", "Click to Switch to Light Mode");
                }
            });
        }
        //===== End Dark Mode toggle script
    });
</script>



<script>
    /////---------------Popups, notifications
    function updateCounter() {
        const notificationCount = document.querySelectorAll('.notifications .notification').length;
        const counter = document.querySelector('.notification-container .counter');
        const exitAllBtn = document.querySelector('.exit-all-btn');

        if (notificationCount > 0) {
            counter.textContent = `${notificationCount} Notification${notificationCount > 1 ? 's' : ''}`;
            counter.style.display = 'block';
            exitAllBtn.style.display = 'block';
        } else {
            counter.style.display = 'none';
            exitAllBtn.style.display = 'none';
        }
    }



    document.querySelector('.exit-all-btn')?.addEventListener('click', function () {
        document.querySelectorAll('.notifications .notification').forEach(function (notification) {
            notification.remove();
        });
        updateCounter();
    });

    /////------Lazy load
    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.hotbutton');

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.click(); // Trigger the button click
                    observer.unobserve(entry.target); // Ensure it only triggers once
                }
            });
        }, {
            threshold: 1.0 // Button must be fully visible in the viewport
        });

        buttons.forEach(button => observer.observe(button));
    });


    //==== disable right-click selectively
    // document.querySelectorAll('.no-right-click').forEach(element => {
    //     element.addEventListener('contextmenu', (e) => {
    //         e.preventDefault();
    //     });
    // })

    /// ==== Disable right click for elements with disabled attribute instead:
    document.querySelectorAll('[disabled]').forEach(element => {
        element.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });
    });

</script>

<!-- --------------Call centre code ---- -->
<?php



if ($call_centre_enabled == 1) {

    //  $token_set = session_variables('ADD', "call_token", "");
    $token_ = cc_token($userd['uid']);

    // $token_ = 'ATCAPtkn_058030b77d5cdfad38681b6123829b290646f857d1942e051f59ae2e74d8df6a';
    // echo $token_."kkk";
    ?>
    <script>
        // Global variables
        let client;  // will hold the Africa's Talking client instance
        const params = {
            sounds: {
                dialing: 'https://superlender.co.ke/zidicash/apis/dial-tone.mp3',
                ringing: 'https://superlender.co.ke/zidicash/apis/ring-tone.mp3'
            }
        };

        // Token for Africa's Talking client
        const token = '<?php echo $token_; ?>';

        // Immediately initialize if the document is already loaded,
        // or wait for DOMContentLoaded if not.
        (function () {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initiateListeners(token);
                });
            } else {
                initiateListeners(token);
            }
        })();

        // Assuming 'client' is your Africa's Talking client instance and has a 'send' method.
        const KEEP_ALIVE_INTERVAL = 60000; // 60 seconds

        setInterval(() => {
            if (client && typeof client.send === 'function') {
                // Sending a dummy keep-alive message. Replace or adjust based on your API.
                client.send({ type: 'keepalive' });
            }
        }, KEEP_ALIVE_INTERVAL);

    </script>

    <div class="popup-box" id="popupBox">
        <a onclick="hide_div('#popupBox')" class="exit-icon" id="exitIcon">&times;</a>
        <h3>Incoming Call</h3>
        <p>From: <span id="incoming_number">...</span></p>
        <div class="buttons">
            <button class="btn btn-success" id="receive_call_btn">Accept</button>
            <button class="btn btn-danger" id="reject_call_btn">Reject</button>
        </div>
    </div>



    <!-- Ensure the Africa's Talking library is loaded first -->
    <script src="https://unpkg.com/africastalking-client@1.0.5/build/africastalking.js"></script>

    <?php
} ?>


<?php

// close current db connection
if (isset($con)) {
    mysqli_close($con);
}
// close archive db querying connection
if (isset($con1)) {
    mysqli_close($con1);
}

// close archive db login connection
if (isset($con2)) {
    mysqli_close($con2);
}
?>
