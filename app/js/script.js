const btnBurger = document.querySelector('#btnBurger');
const body = document.querySelector('body');
const header = document.querySelector('.header');
const overlay = document.querySelector('.overlay');
const fadeElements = document.querySelectorAll('.has-fade');

btnBurger.addEventListener('click', function(){
    console.log('click burger!');

    if(header.classList.contains('open')) {  // Closes the Burger Menu

        body.classList.add('noScroll');
        header.classList.remove('open');
        fadeElements.forEach(function(element){

            element.classList.remove('fade-in');
            element.classList.add('fade-out');
        });
    }
    else {  // Opens the Burger Menu
        body.classList.add('noScroll');
        header.classList.add('open');
        fadeElements.forEach(function(element){

            element.classList.remove('fade-out');
            element.classList.add('fade-in');
        });
    }
});



var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    } 
  });
}