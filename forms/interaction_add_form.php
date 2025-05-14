<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
/////----------Session Check
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$customer_id = $_POST['customer_id'];
if ($customer_id  > 0) {
    $cust = fetchonerow('o_customers', "uid='$customer_id'", "uid, national_id, full_name");
    $client_details = $cust['full_name'] . ' (ID: ' . $cust['national_id'] . ')';
    $client_id = $cust['uid'];
}
/////---------End of session check
?>
<?php
if ($customer_id > 0) {
?>
    <div class="box-body" style="background: #f4f4f4;    padding: 2px;
    width: 100%;
    display: block;
    overflow-x: scroll;">
        <table style="width: 100%;" class="text-bold;">
            <tr>
                <td>Tag</td>
                <?php


                // 3, 8, 9
                $badges = fetchtable('o_badges', "status= 1 AND uid in (3,5,8,9)", "uid", "asc", "1000");
                while ($b = mysqli_fetch_array($badges)) {
                    $bid = $b['uid'];
                    $title = $b['title'];
                    $description = $b['description'];
                    $icon = $b['icon'];

                    echo "<td><a  class='btn btn-sm btn-default' title=\"$description\" onclick=\"tag_client($client_id, $bid)\"> <img height='20px' src=\"badges/$icon\"><span class='text-black font-bold'>$title</span></a></td>";
                }

                echo "<td><a  class='btn btn-default  bg-warning' title=\"Remove badge\" onclick=\"tag_client($client_id, 0)\"><img height='20px' src=\"badges/remove.png\"><span class='text-black font-bold'></span></a></td>";
                ?>
            </tr>
        </table>
    </div>
<?php
}
?>
<form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
    <div class="box-body">
        <div class="form-group">
            <label for="customer" class="col-sm-3 control-label">Customer</label>

            <div class="col-sm-9">
                <input class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" placeholder="Start typing customer name ...">
                <input type="hidden" id="customer_id_">
                <div id="customer_results">

                </div>
            </div>

        </div>
        <div class="form-group">
            <label for="conversation_method" class="col-sm-3 control-label">Conversation Method</label>

            <div class="col-sm-9">
                <select class="form-control" id="conv_method">
                    <option value="0">--Select One</option>
                    <?php
                    $o_conversation_methods_ = fetchtable('o_conversation_methods', "status=1", "uid", "desc", "0,100", "uid ,name , details,status ");
                    while ($n = mysqli_fetch_array($o_conversation_methods_)) {
                        $uid = $n['uid'];
                        $name = $n['name'];
                        $status = $n['status'];
                        $details = $n['details'];

                        echo "<option value='$uid' class='radio-box'>$name</option>";
                    }
                    ?>
                </select>



            </div>
        </div>
        <div class="form-group">
            <label for="conversation_purpose" class="col-sm-3 control-label">Conversation Purpose</label>

            <div class="col-sm-9">
                <select class="form-control" id="conversation_purpose">
                    <option value="0">--Select One</option>

                    <?php
                    $o_flags_ = fetchtable('o_conversation_purpose', "status=1", "uid", "desc", "0,100", "uid ,name");
                    while ($n = mysqli_fetch_array($o_flags_)) {
                        $uid = $n['uid'];
                        $name = $n['name'];
                        $description = $n['description'];
                        $color_code = $n['color_code'];

                        echo "<option value='$uid'>$name</option>";

                    }
                    ?>


                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="default_reason" class="col-sm-3 control-label">Reason for Default</label>

            <div class="col-sm-9">
                <select class="form-control" id="default_reason">
                    <option value="0">--Select One</option>

                    <?php
                    $o_reasons_ = fetchtable('o_default_reasons', "status=1", "uid", "desc", "0,100", "uid ,name");
                    while ($n = mysqli_fetch_array($o_reasons_)) {
                        $uid = $n['uid'];
                        $name = $n['name'];
                        $description = $n['description'];
                        $color_code = $n['color_code'];

                        echo "<option value='$uid'>$name</option>";

                    }
                    ?>


                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="conversation_outcome" class="col-sm-3 control-label">Outcome</label>

            <div class="col-sm-9">
                <select class="form-control" id="conversation_outcome">
                    <option value="0">--Select One</option>

                    <?php
                    $o_flags_ = fetchtable('o_flags', "uid>0", "uid", "desc", "0,100", "uid ,name ,description ,color_code ");
                    while ($n = mysqli_fetch_array($o_flags_)) {
                        $uid = $n['uid'];
                        $name = $n['name'];
                        $description = $n['description'];
                        $color_code = $n['color_code'];

                        echo "<option value='$uid'>$name</option>";
                    }
                    ?>


                </select>


            </div>
        </div>
        <div class="form-group">
            <label for="details" class="col-sm-3 control-label">Outcome/Details</label>

            <div class="col-sm-9">
                <textarea class="form-control" id="details"></textarea>
              <div class="help-block">
                <div class="row">
                    <div class="col-sm-6"><a class="font-18 text-red"><i class="fa fa-microphone"></i></a> <span class="text-purple" id="error-message">Record conversation</span> </div>
                    <div class="col-sm-6"><button id="start" class="btn bg-green-gradient btn-sm font-bold">Start</button>
                                          <button class="btn bg-blue-gradient btn-sm font-bold" id="stop">Stop</button>
                                          <button class="btn bg-red-gradient btn-sm font-bold pull-right" id="clear">Clear</button></div>
                </div>
              </div>

            </div>
        </div>
        <div class="form-group">
            <label for="next_int" class="col-sm-3 control-label">Next Interaction</label>

            <div class="col-sm-9">
                <input type="datetime-local" class="form-control" id="next_int">
            </div>
        </div>
        <div class="form-group">
            <label for="next_stage" class="col-sm-3 control-label">Next Steps</label>

            <div class="col-sm-9">
                <select class="form-control" id="next_stage">
                    <option value="0">--Select One</option>
                    <?php
                    $o_next_steps_ = fetchtable('o_next_steps', "status = 1", "uid", "desc", "0,100", "uid ,name ,details ");
                    while ($a = mysqli_fetch_array($o_next_steps_)) {
                        $uid = $a['uid'];
                        $name = $a['name'];
                        $details = $a['details'];
                        echo "<option value='$uid'>$name</option>";
                    }

                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="promised_amount" class="col-sm-3 control-label">Promised Amount</label>

            <div class="col-sm-9">
                <input type="number" class="form-control" id="promised_amount">
            </div>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <div class="box-footer">
                <br />
                <!-- <button type="submit" class="btn btn-lg btn-default">Cancel</button> -->
                <button id="save_interaction_btn" type="submit" class="btn btn-success btn-lg pull-right" onclick="save_interaction('<?php echo $customer_id; ?>');">
                    <span class="spinner-border hidden"></span>
                    <span class="btn-txt">Save</span>
                </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>
<script>
    if ('<?php echo $client_id; ?>') {
        select_client('<?php echo $client_details ?>', '<?php echo $client_id; ?>');
    }
</script>


<script>
    if ('webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();

        recognition.continuous = true;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        let lastTimestamp = 0;
        let lastCharWasPunctuation = false;
        let lastTranscript = '';
        let recognizing = false;

        function adjustTextareaHeight() {
            const textarea = document.getElementById('details');
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        function capitalizeAfterPeriod(text) {
            return text.replace(/(\.\s+)(\w)/g, (match, separator, char) => separator + char.toUpperCase());
        }

        function addPunctuation(transcript, event) {
            let currentTime = event.timeStamp;
            let timeDifference = currentTime - lastTimestamp;

            if (!lastCharWasPunctuation) {
                if (timeDifference > 1500) {
                    transcript = transcript.trim() + '. ';
                    lastCharWasPunctuation = true;
                } else if (timeDifference > 500) {
                    transcript = transcript.trim() + ', ';
                    lastCharWasPunctuation = true;
                }
            }

            transcript = capitalizeAfterPeriod(transcript);

            lastTimestamp = currentTime;
            return transcript;
        }

        document.getElementById('start').onclick = () => {
            if (!recognizing) {
                recognition.start();
                recognizing = true;
                lastTimestamp = 0;
                lastCharWasPunctuation = false;
                lastTranscript = '';
                document.getElementById('error-message').innerText = 'Listening... Please speak into the microphone.';
                console.log('Voice recognition started');
            }
        };

        document.getElementById('stop').onclick = () => {
            if (recognizing) {
                recognition.stop();
                recognizing = false;
                document.getElementById('error-message').innerText = 'Recognition stopped. Please click "Start Recording" to resume.';
                console.log('Voice recognition stopped');
            }
        };

        document.getElementById('clear').onclick = () => {
            document.getElementById('details').value = '';
            adjustTextareaHeight();
            console.log('Textarea cleared');
        };

        recognition.onresult = (event) => {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    let tempTranscript = event.results[i][0].transcript.trim();

                    // Prevent repeated phrases
                    if (tempTranscript !== lastTranscript) {
                        transcript += addPunctuation(tempTranscript, event);
                        lastTranscript = tempTranscript;
                    }
                }
            }

            const textarea = document.getElementById('details');
            textarea.value += transcript;
            adjustTextareaHeight();
            lastCharWasPunctuation = false;
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            let errorMessage = 'Speech recognition error: ' + event.error;
            if (event.error === 'no-speech') {
                errorMessage = 'No speech detected. Please try again.';
            } else if (event.error === 'audio-capture') {
                errorMessage = 'No microphone found. Ensure that a microphone is connected and try again.';
            } else if (event.error === 'not-allowed') {
                errorMessage = 'Permission to use the microphone is denied. Please allow access to the microphone.';
            }
            document.getElementById('error-message').innerText = errorMessage;
        };

        recognition.onend = () => {
            if (recognizing) {
                console.log('Voice recognition ended, restarting...');
                recognition.start();
            } else {
                console.log('Voice recognition stopped by user.');
            }
        };

        document.getElementById('details').addEventListener('input', adjustTextareaHeight);
    } else {
        document.getElementById('error-message').innerText = 'Your browser does not support speech recognition. Please use Google Chrome.';
    }
</script>