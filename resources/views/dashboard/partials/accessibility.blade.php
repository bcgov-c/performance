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


.focusable {
    /* Define styles for when the element is focused */
    outline: 2px solid blue; /* Change the color and style as needed */
}

/* Apply a border and outline when the element is focused */
button:focus,
button:active {
    outline: none; /* Remove the default focus outline */
    border: 2px solid blue; /* Add a border when the element is focused */
    /* Add any other styles you want to apply when the element is focused */
}

.close-white {
    color: white;
}

.btn-danger:focus {
    outline: none; /* Remove the default outline */
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5); /* Add a red box shadow when focused */
}

.btn-primary:focus {
    outline: none; /* Remove the default outline */
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5); /* Add a red box shadow when focused */
}
 
</style>



<script>
/*
 *   This content is licensed according to the W3C Software License at
 *   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 *   File:   Checkbox.js
 *
 *   Desc:   Checkbox widget that implements ARIA Authoring Practices
 */

 'use strict';

class Checkbox {
  constructor(domNode) {
    this.domNode = domNode;
    this.domNode.tabIndex = 0;

    if (!this.domNode.getAttribute('aria-checked')) {
      this.domNode.setAttribute('aria-checked', 'false');
    }

    this.domNode.addEventListener('keydown', this.onKeydown.bind(this));
    this.domNode.addEventListener('keyup', this.onKeyup.bind(this));
    this.domNode.addEventListener('click', this.onClick.bind(this));
  }

  toggleCheckbox() {
    if (this.domNode.getAttribute('aria-checked') === 'true') {
      this.domNode.setAttribute('aria-checked', 'false');
    } else {
      this.domNode.setAttribute('aria-checked', 'true');
    }
  }

  /* EVENT HANDLERS */

  // Make sure to prevent page scrolling on space down
  onKeydown(event) {
    if (event.key === ' ') {
      event.preventDefault();
    }
  }

  onKeyup(event) {
    var flag = false;

    switch (event.key) {
      case ' ':
        this.toggleCheckbox();
        flag = true;
        break;

      default:
        break;
    }

    if (flag) {
      event.stopPropagation();
    }
  }

  onClick() {
    this.toggleCheckbox();
  }
}

// Initialize checkboxes on the page
window.addEventListener('load', function () {
  let checkboxes = document.querySelectorAll('.checkboxes [role="checkbox"]');
  for (let i = 0; i < checkboxes.length; i++) {
    new Checkbox(checkboxes[i]);
  }
});


</script>

