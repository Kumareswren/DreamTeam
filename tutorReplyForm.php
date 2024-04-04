<?php
$output = '<form id="messageForm" enctype="multipart/form-data">'; 
$output .= '<div class="form-group mt-3">';
$output .= '<label for="userMessage">Enter your message here:</label>';
$output .= '<textarea class="form-control" id="userMessage" name="userMessage" rows="5" placeholder="Type your message here..."></textarea>';
$output .= '</div>';
$output .= '<button type="button" class="btn btn-primary mt-3" onclick="submitMessage()">Send Message</button>';
$output .= '</form>';
echo $output;
?>

