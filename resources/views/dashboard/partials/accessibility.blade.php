<style>
ul.checkboxes {
  list-style: none;
  margin: 0;
  padding: 0;
  padding-left: 1em;
}

ul.checkboxes li {
  list-style: none;
  margin: 1px;
  padding: 0;
}

ul.checkboxes li label {
  display: inline-block;
  padding: 4px 8px;
  cursor: pointer;
}

ul.checkboxes li input[type="checkbox"] {
  display: inline-block;
}

ul.checkboxes li input[type="checkbox"] + label::before {
  position: relative;
  top: 1px;
  left: -4px;
  content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='16' width='16' style='forced-color-adjust: auto;'%3E%3Crect x='2' y='2' height='13' width='13' rx='2' stroke='currentcolor' stroke-width='1' fill-opacity='0' /%3E%3C/svg%3E");
}

ul.checkboxes li input[type="checkbox"]:checked + label::before {
  position: relative;
  top: 1px;
  content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='16' width='16' style='forced-color-adjust: auto;'%3E%3Crect x='2' y='2' height='13' width='13' rx='2' stroke='currentcolor' stroke-width='1' fill-opacity='0' /%3E%3Cpolyline points='4,8 7,12 12,5' fill='none' stroke='currentcolor' stroke-width='2' /%3E%3C/svg%3E");
}

ul.checkboxes li label:focus,
ul.checkboxes li label:hover {
  padding: 2px 6px;
  border: 2px solid #005a9c;
  border-radius: 5px;
  background-color: #def;
}

ul.checkboxes li label:hover {
  cursor: pointer;
}

</style>

