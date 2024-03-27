<?php
session_start();
require_once('db.php'); // Include the database connection file
require_once('vendor/autoload.php'); // Include the JWT library
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
function Chat() {
    // Simulated previous messages
    $previousMessages = [
        ['sender' => 'tutor', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'student', 'message' => 'Sure, I can help you with that.'],
        ['sender' => 'tutor', 'message' => 'Great! When can we discuss?'],
        ['sender' => 'student', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'tutor', 'message' => 'Sure, I can help you with that.'],
        ['sender' => 'student', 'message' => 'Hi, I need help with my assignment.'],
        ['sender' => 'tutor', 'message' => 'Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.Sure, I can help you with that.']
        

    ];

    $output = '<style>';
    $output .= '.chat-container {';
    $output .= '  display: flex;';
    $output .= '  flex-direction: column;';
    $output .= '  max-width: 800px;';
    $output .= '  margin: auto;';
    $output .= '  border: 2px solid #333;';
    $output .= '  border-radius: 10px;';
    $output .= '  overflow: hidden;';
    $output .= '  font-family: Arial, sans-serif;';
    $output .= '  font-size: 16px;';
    $output .= '}';
    $output .= '.chat-box {';
    $output .= '  width: 100%;';
    $output .= '  height: 400px;';
    $output .= '  overflow-y: auto;'; // Changed from 'scroll' to 'auto'
    $output .= '  padding: 10px;';
    $output .= '  background-color: #f9f9f9;';
    $output .= '}';
    $output .= '.chat-message {';
    $output .= '  padding: 10px;';
    $output .= '  clear: both;';
    $output .= '}';
    $output .= '.chat-bubble {';
    $output .= '  display: inline-block;';
    $output .= '  margin-bottom: 10px;';
    $output .= '  padding: 15px;';
    $output .= '  border-radius: 20px;';
    $output .= '  color: #fff;';
    $output .= '  word-wrap: break-word;';
    $output .= '}';
    $output .= '.you {';
    $output .= '  float: left;';
    $output .= '  text-align: left;';
    $output .= '  background-color: #007bff;';
    $output .= '}';
    $output .= '.me {';
    $output .= '  float: right;';
    $output .= '  text-align: right;';
    $output .= '  background-color: #28a745;';
    $output .= '}';

    $output .= '.chat-input {';
        $output .= '  display: flex;'; // Use flexbox to align items
        $output .= '  align-items: center;'; // Center items vertically
        $output .= '  padding: 10px;';
        $output .= '  border-top: 2px solid #333;';
        $output .= '}';

$output .= '.chat-input textarea {';
$output .= '  flex: 1;'; // Take remaining space
$output .= '  height: auto;';
$output .= '  padding: 10px;';
$output .= '  border: 2px solid #333;';
$output .= '  border-radius: 10px;';
$output .= '  margin-right: 5px;';
$output .= '  overflow: hidden;';
$output .= '}';

$output .= '.send-btn {';
$output .= '  padding: 10px;';
$output .= '  border: none;';
$output .= '  border-radius: 10px;';
$output .= '  background-color: #007bff;';
$output .= '  color: #fff;';
$output .= '  cursor: pointer;';
$output .= '  font-weight: bold;';
$output .= '}';


$output .= '.send-btn:hover {';
    $output .= '  background-color: #0056b3;';
    $output .= '}';
    
$output .= '.search-box {';
$output .= '  width: 100%;';
$output .= '  padding: 10px;';
$output .= '  border-bottom: 2px solid #333;';
$output .= '}';

$output .= '.search-box input[type="text"] {';
$output .= '  width: 100%;';
$output .= '  padding: 10px;';
$output .= '  border: 2px solid #333;';
$output .= '  border-radius: 10px;';
$output .= '}';

    $output .= '.tutor-name {';
    $output .= '  padding: 10px;';
    $output .= '  border-bottom: 2px solid #333;'; // Adding bottom border
    $output .= '  background-color: #f9f9f9;';
    $output .= '  text-align: center;';
    $output .= '  font-weight: bold;';
    $output .= '}';
    $output .= '</style>';

    $output .= '<div class="chat-container">';
    $output .= '<div class="chat-box" id="chatBox">';
    $output .= '<div class="tutor-name">';
    $output .= '  Tutor Name Here'; // Replace this with the actual tutor name
    $output .= '</div>';
    // Previous messages
    foreach ($previousMessages as $message) {
        $senderClass = ($message['sender'] == 'tutor') ? 'you' : 'me';
        $output .= '<div class="chat-message">';
        $output .= "<div class='chat-bubble $senderClass'>{$message['message']}</div>";
        $output .= '</div>';
    }
    $output .= '</div>'; // Closing chat-box div
    $output .= '<div class="chat-input">';
    $output .= '<textarea id="chatInput" placeholder="Type your message..." rows="4"></textarea>';
    $output .= '<button class="send-btn" onclick="sendMessage()">Send</button>';
    $output .= '</div>'; // Closing chat-input div
    $output .= '</div>'; // Closing chat-container div
    // JavaScript for handling Tutor click event
    $output .= '<script>';
    $output .= '$(document).ready(function() {';
    $output .= '  // Your JavaScript code here';
    $output .= '});';
    $output .= '</script>';
    return $output;
}
// Call the function to generate the UI
echo Chat();
?>
