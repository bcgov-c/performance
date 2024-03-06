<style>
[role="checkbox"] {
  cursor: pointer;
}

[role="checkbox"]:focus {
  outline: 2px solid blue; /* Example focus styling */
}

[aria-checked="false"]::before {
  content: "\2610"; /* Unicode for empty checkbox */
}

[aria-checked="true"]::before {
  content: "\2611"; /* Unicode for checked checkbox */
}

.checkbox-container {
    display: inline-flex;
    align-items: center; /* Align items vertically center */
}

.checkbox-label {
    margin-left: 5px; /* Add space between checkbox and label */
}


</style>

