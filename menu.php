<aside class="main-sidebar" id="left_menu">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar" style="height: auto; line-height:30px;font-weight:600;">
        <!-- Sidebar user panel -->

        <!-- search form -->

        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">

            <?php

            $usergroup__ = $userd['user_group'];
            $userid__ = $userd['uid'];
            $menu = fetchonerow('o_menu',"(gid='$usergroup__' OR sid='$userid__') AND status=1","uid, visible_menu, dashboard");
            if($menu['uid'] > 0){

                $dashboard = $menu['dashboard'];
                $menu_items = $menu['visible_menu'];
                $jso = json_decode($menu_items, true);

                foreach ($jso as $link__ => $men__) {
                    echo " <li>
                    <a href=\"$link__\">
                       $men__
                    </a>
                </li>";
                }

            }
            else{

            ?>

            <li class="header">MAIN NAVIGATION  <?php echo $ai_enabled ? "<a href='ai' class='ai-icon'><img src='custom_icons/robo.png' height='20px'>Try AI</a>" : "" ?> </li>

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-users"></i>
                    <span>Customers</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>

                </a>
                <ul class="treeview-menu">
                    <li><a href="customers"><i class="fa fa-users text-green"></i>Active Customers</a></li>
                    <li><a href="leads"><i class="fa fa-users text-blue"></i> Leads</a></li>
                    <?php
                    if($group_loans == 1){
                        ?>
                        <li>
                            <a href="groups">
                                <i class="fa fa-group text-orange"></i>
                                <span>Groups</span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <!-- <li><a href="scoring"><i class="fa fa-users text-red"></i> Scoring</a></li> -->
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fa fa-hand-pointer-o"></i>
                    <span>CRM</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>

                </a>
                <ul class="treeview-menu">
                    <li style="display: none;"><a href="customers?type=leads"><i class="fa fa-circle-o"></i> Leads</a></li>
                    <li><a href="interactions"><i class="fa fa-circle-o"></i> Interactions</a></li>
                    <li><a href="broadcasts"><i class="fa fa-circle-o"></i> Broadcasts</a></li>
                    <li style="display: none;"><a href="tickets"><i class="fa fa-circle-o"></i> Tickets</a></li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-money"></i>
                    <span id="loa_">Loans</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="assets"><i class="fa fa-circle-o"></i> Assets</a></li>
                    <li><a href="loans"><i class="fa fa-circle-o"></i> All Loans</a></li>
                    <li><a href="defaulters" class="text-red"><i class="fa fa-circle-o"></i> Defaulters</a></li>
                    <li><a href="installments" class="text-orange"><i class="fa fa-circle-o"></i> Installments</a></li>
                    <!-- <li><a href="installments-v2" class="text-blue"><i class="fa fa-circle-o"></i> Installments V2</a></li> -->
                    <li><a href="falling-due" class="text-green"><i class="fa fa-circle-o"></i> Falling Due</a></li>
                    <li><a href="loans?approvals"><i class="fa fa-circle-o"></i> Approvals</a></li>

                    <?php
                    if($asset_loans == 1){
                        ?>

                        <!-- <li class="treeview">
                            <a href="#">
                                <i class="fa fa-shopping-bag text-orange"></i>
                                <span>Asset Finance</span>
                                <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>

                            </a>
                            <ul class="treeview-menu">
                                <li><a href="assets.php?cat=summary"><i class="fa fa-circle-o"></i> Summary</a></li>
                                <li><a href="assets.php?cat=assets"><i class="fa fa-circle-o"></i> Assets</a></li>

                            </ul>
                        </li> -->
                        <?php
                    }
                    ?>
                    <li><a href="loan-products"><i class="fa fa-circle-o"></i> Loan Products</a></li>
                </ul>
            </li>

            <li>
                <a href="incoming-payments">
                    <i class="fa fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-sitemap"></i>
                    <span>Organization</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>

                </a>
                <ul class="treeview-menu">
                    <li><a href="staff"><i class="fa fa-circle-o"></i> Staff</a></li>
                    <li><a href="branches"><i class="fa fa-circle-o"></i> Branches</a></li>
                </ul>

            </li>
            <li class="treeview">
            <a href="#">
                    <i class="fa fa-line-chart"></i>
                    <span id="rep_">Reports</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>

                </a>
                <ul class="treeview-menu">
                    <li><a href="reports"><i class="fa fa-circle-o"></i> All Reports</a></li>
                    <li><a href="allocations-v2"><i class="fa fa-circle-o"></i> Allocations</a></li>
                </ul>
            </li>
            <li class="treeview">
            <a href="#">
                    <i class="fa  fa-balance-scale"></i>
                    <span id="acc_">Accounting</span>
                    <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
                </a>
                <ul class="treeview-menu">

                    <li style="display: none;"><a href="monthly-performance"><i class="fa fa-circle-o"></i> Monthly Performance </a></li>
                    <li style="display: none;"><a href="bdo-performance"><i class="fa fa-circle-o"></i> BDO Performance </a></li>
                    <li><a href="accounting?general-ledger"><i class="fa fa-circle-o"></i> General Ledger</a></li>
                    <li><a href="accounting?income-statement"><i class="fa fa-circle-o"></i> Income Statement</a></li>
                    <li><a href="accounting?accounts-receivable"><i class="fa fa-circle-o"></i> Accounts Receivables</a></li>
                    <li><a href="accounting?balance-sheet"><i class="fa fa-circle-o"></i> Balance Sheet</a></li>
                    <li><a href="accounting?cash-flow"><i class="fa fa-circle-o"></i> Cash Flow</a></li>
                    <li><a href="accounting?defaulter-ageing"><i class="fa fa-circle-o"></i> Defaulter Ageing</a></li>
                    <li><a href="accounting?trial-balance"><i class="fa fa-circle-o"></i> Trial Balance</a></li>
                    <li><a href="audit_report"><i class="fa fa-bug text-red"></i> Audit</a></li>
                    <li><a href="expenses"><i class="fa fa-calculator text-green"></i> Expenses</a></li>


                </ul>
            </li>
            <li>
                <a href="settings">
                    <i class="fa fa-cog"></i>
                    <span>Settings</span>

                </a>

            </li>
        </ul>
        <?php
        }
        ?>
    </section>
</aside>