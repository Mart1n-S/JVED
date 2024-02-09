function changeVisibility() {
    var passwordInputs = document.getElementsByClassName('password');

    // Vérifie le type actuel du premier champ de mot de passe
    var currentType = passwordInputs[0].type;

    // Bascule entre 'password' et 'text'
    var newType = (currentType === 'password') ? 'text' : 'password';

    // Applique le nouveau type à tous les champs de mot de passe
    for (var i = 0; i < passwordInputs.length; i++) {
        passwordInputs[i].type = newType;
    }
}

function updatePasswordCriteria() {
    var passwordRequirements = document.getElementById('passwordRequirements');

    // Vérifier et appliquer la couleur en fonction des critères
    checkAndApplyColor('lowercase', /(?=.*[a-z])/);
    checkAndApplyColor('uppercase', /(?=.*[A-Z])/);
    checkAndApplyColor('digit', /(?=.*\d)/);
    checkAndApplyColor('specialChar', /(?=.*[\W_])/);
    checkAndApplyColor('length', /^.{8,24}$/);

    // Vérifier si toutes les conditions sont remplies
    var allConditionsMet = Array.from(passwordRequirements.children).every(function(element) {
        return element.classList.contains('valid');
    }); 

    // Afficher ou masquer les critères du mot de passe en fonction des résultats
    if (allConditionsMet) {
        passwordRequirements.style.display = 'none';
    } else {
        passwordRequirements.style.display = 'flex';
    }
}

function checkAndApplyColor(elementId, condition) {
    var element = document.getElementById(elementId);
    var passwordInput = document.getElementById('password');

    // Appliquer la classe CSS "valid" si la condition est remplie, sinon "invalid"
    if (condition.test(passwordInput.value)) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

function validatePasswordMatch() {
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('confirmPassword');
    var passwordMatchMessage = document.getElementById('passwordMatchMessage');

    // Vérifier si les mots de passe correspondent
    if (passwordInput.value === confirmPasswordInput.value) {
        passwordMatchMessage.style.display = 'none';
    } else {
        passwordMatchMessage.style.display = 'block';
    }
}

function validateForm() {
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('confirmPassword');
    var passwordMatchMessage = document.getElementById('passwordMatchMessage');
    var errorMessage = document.getElementById('error');

    // Vérifier si tous les champs sont remplis
    var allFieldsFilled = Array.from(document.querySelectorAll('.inscriptionContentFormInput input')).every(function(input) {
        return input.value.trim() !== '';
    });

    if (!allFieldsFilled) {
       errorMessage.style.display = 'block'
        return false; // Empêcher la soumission du formulaire
    }else{
        errorMessage.style.display = 'none'
    }

    // Vérifier une dernière fois les mots de passe avant la soumission du formulaire
    if (passwordInput.value !== confirmPasswordInput.value) {
        passwordMatchMessage.style.display = 'block';
        return false; // Empêcher la soumission du formulaire
    }

    return true; // Autoriser la soumission du formulaire
}
