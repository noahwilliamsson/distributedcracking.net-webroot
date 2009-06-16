<?
        // Display diagnostic messages, if any
        if(isset($_SESSION["info"])) {
                $msg = $_SESSION["info"];
                unset($_SESSION["info"]);
?>
        <div class="info">
                <p><?= htmlspecialchars($msg) ?></p>
        </div>
<?php
        }
        else if(isset($_SESSION["error"])) {
                $msg = $_SESSION["error"];
                unset($_SESSION["error"]);
?>
        <div class="note">
                <p><?= htmlspecialchars($msg) ?></p>
        </div>
<?php
        }
?>
