var inputPicture = document.getElementById("user_file") || document.getElementById("tool_file");
console.log(inputPicture);

var image = document.getElementById("afterSelect");
image.style.display = 'none';
inputPicture.addEventListener("change", function readURL(e) {
  var reader = new FileReader();
  reader.onload = function() {
    var output = document.getElementById("afterSelect");
    output.src = reader.result;
    image.style.display = 'block';
  };
  reader.readAsDataURL(event.target.files[0]);
});
