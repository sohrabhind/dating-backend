<?php

    

    

?>

<aside class="left-sidebar">

    <div class="scroll-sidebar"> <!-- Sidebar scroll-->

        <nav class="sidebar-nav"> <!-- Sidebar navigation-->

            <ul id="sidebarnav">

                <li class="nav-small-cap">General</li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "main") { echo "active";} ?>" href="/admin/main" aria-expanded="false">
                            <i class="ti-dashboard"></i>
                            <span class="hide-menu">Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "chats_b") { echo "active";} ?>" href="/admin/chats_b" aria-expanded="false">
                            <i class="ti-comment-alt"></i>
                            <span class="hide-menu">Chats B</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "upgrades") { echo "active";} ?>" href="/admin/upgrades" aria-expanded="false">
                            <i class="ti-star"></i>
                            <span class="hide-menu">Upgrades</span>
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "gcm") { echo "active";} ?>" href="/admin/gcm" aria-expanded="false">
                            <i class="ti-bell"></i>
                            <span class="hide-menu">Push Notifications</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "users") { echo "active";} ?>" href="/admin/users" aria-expanded="false">
                            <i class=" ti-search"></i>
                            <span class="hide-menu">Search Users</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "support") { echo "active";} ?>" href="/admin/support" aria-expanded="false">
                            <i class="ti-help-alt"></i>
                            <span class="hide-menu">Support</span>
                        </a>
                    </li>

                <li class="nav-devider"></li>

                <li class="nav-small-cap">Stream</li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "images") { echo "active";} ?>" href="/admin/images" aria-expanded="false">
                            <i class="ti-image"></i>
                            <span class="hide-menu">Images Stream</span>
                        </a>
                    </li>

                    
                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "chats") { echo "active";} ?>" href="/admin/chats" aria-expanded="false">
                            <i class="ti-comment-alt"></i>
                            <span class="hide-menu">Chats Stream</span>
                        </a>
                    </li>

                    

                <li class="nav-devider"></li>

                <li class="nav-small-cap">Reports</li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "reports") { echo "active";} ?>" href="/admin/profile_reports" aria-expanded="false">
                            <i class="ti-face-sad"></i>
                            <span class="hide-menu">Profile Reports</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "image_reports") { echo "active";} ?>" href="/admin/image_reports" aria-expanded="false">
                            <i class="ti-gallery"></i>
                            <span class="hide-menu">Photo Reports</span>
                        </a>
                    </li>

                <li class="nav-devider"></li>

                <li class="nav-small-cap">Mobile</li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "purchases") { echo "active";} ?>" href="/admin/purchases" aria-expanded="false">
                            <i class="ti-money"></i>
                            <span class="hide-menu">In-app Purchases</span>
                        </a>
                    </li>
                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "app") { echo "active";} ?>" href="/admin/app" aria-expanded="false">
                            <i class="ti-mobile"></i>
                            <span class="hide-menu">App Settings</span>
                        </a>
                    </li>

                <li class="nav-devider"></li>

                <li class="nav-small-cap">Settings</li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "settings") { echo "active";} ?>" href="/admin/settings" aria-expanded="false">
                            <i class="ti-settings"></i>
                            <span class="hide-menu">Settings</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark <?php if (isset($page_id) && $page_id === "admins") { echo "active";} ?>" href="/admin/admins" aria-expanded="false">
                            <i class="ti-crown"></i>
                            <span class="hide-menu">Administrators</span>
                        </a>
                    </li>

                    <li>
                        <a class="waves-effect waves-dark" href="/admin/logout?access_token=<?php echo admin::getAccessToken(); ?>&continue=/" aria-expanded="false">
                            <i class="ti-power-off"></i>
                            <span class="hide-menu">Logout</span>
                        </a>
                    </li>

            </ul>
        </nav> <!-- End Sidebar navigation -->
    </div> <!-- End Sidebar scroll-->
</aside>