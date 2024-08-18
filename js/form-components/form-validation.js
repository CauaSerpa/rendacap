// Form Validation

$(document).ready(() => {
  // Máscaras para CPF, RG e Telefone
  $('#cpf').mask('000.000.000-00');
  $('#rg').mask('00.000.000-0');
  $('#phone').mask('(00) 00000-0000');
  $('#birth_date').mask('00/00/0000');

  // Validação de data de nascimento para garantir que o usuário tenha 18 anos ou mais
  $.validator.addMethod("olderThan18", function(value, element) {
    let parts = value.split("/");
    let inputDate = new Date(parts[2], parts[1] - 1, parts[0]);
    let today = new Date();
    let age = today.getFullYear() - inputDate.getFullYear();
    let month = today.getMonth() - inputDate.getMonth();
    if (month < 0 || (month === 0 && today.getDate() < inputDate.getDate())) {
        age--;
    }
    return this.optional(element) || (age >= 18);
  }, "Você deve ter pelo menos 18 anos para se cadastrar.");

  // Validação do Formulário
  $("#signupForm").validate({
    rules: {
      firstname: "required",
      lastname: "required",
      username: {
        required: true,
        minlength: 2,
      },
      password: {
        required: true,
        minlength: 5,
      },
      confirm_password: {
        required: true,
        minlength: 5,
        equalTo: "#password",
      },
      email: {
        required: true,
        email: true,
      },
      confirm_email: {
        required: true,
        email: true,
        equalTo: "#email",
      },
      phone: {
        required: true,
        minlength: 14,  // "(xx) xxxxx-xxxx"
      },
      birth_date: {
        required: true,
        dateBR: true,  // Validação para formato brasileiro
        olderThan18: true,  // Validação para maior de 18 anos
      },
      cpf: {
        required: true,
        cpfBR: true,  // Validação para CPF brasileiro
      },
      rg: {
        required: true,
        minlength: 9,  // "xx.xxx.xxx-x"
      },
      agree: "required",
    },
    messages: {
      firstname: "Por favor, insira seu nome",
      lastname: "Por favor, insira seu sobrenome",
      username: {
        required: "Por favor, insira um nome de usuário",
        minlength: "Seu nome de usuário deve ter pelo menos 2 caracteres",
      },
      password: {
        required: "Por favor, insira uma senha",
        minlength: "Sua senha deve ter pelo menos 5 caracteres",
      },
      confirm_password: {
        required: "Por favor, confirme sua senha",
        minlength: "Sua senha deve ter pelo menos 5 caracteres",
        equalTo: "Por favor, insira a mesma senha acima",
      },
      email: "Por favor, insira um endereço de email válido",
      confirm_email: {
        required: "Por favor, confirme seu email",
        equalTo: "Por favor, insira o mesmo email acima",
      },
      phone: "Por favor, insira um número de telefone válido",
      birth_date: {
        required: "Por favor, insira uma data de nascimento válida",
        olderThan18: "Você deve ter pelo menos 18 anos para se cadastrar.",
      },
      cpf: "Por favor, insira um CPF válido",
      rg: "Por favor, insira um RG válido",
      agree: "Por favor, aceite nossos termos e condições",
    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        error.insertAfter(element.next("label"));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
  });

  // Validação do Formulário
  $("#signinForm").validate({
    rules: {
      email: {
        required: true,
        email: true,
      },
      password: {
        required: true,
      },
    },
    messages: {
      email: "Por favor, insira um endereço de email válido",
      password: {
        required: "Por favor, insira uma senha",
      },
    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        error.insertAfter(element.next("label"));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
  });

  // Validação do Formulário
  $("#recupPassForm").validate({
    rules: {
      email: {
        required: true,
        email: true,
      },
    },
    messages: {
      email: "Por favor, insira um endereço de email válido",
    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        error.insertAfter(element.next("label"));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
  });

  // Validação do Formulário
  $("#resetPassForm").validate({
    rules: {
      password: {
        required: true,
        minlength: 5,
      },
      confirm_password: {
        required: true,
        minlength: 5,
        equalTo: "#password",
      },
    },
    messages: {
      password: {
        required: "Por favor, insira uma senha",
        minlength: "Sua senha deve ter pelo menos 5 caracteres",
      },
      confirm_password: {
        required: "Por favor, confirme sua senha",
        minlength: "Sua senha deve ter pelo menos 5 caracteres",
        equalTo: "Por favor, insira a mesma senha acima",
      },
    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        error.insertAfter(element.next("label"));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
  });

  // Adiciona métodos personalizados para validações brasileiras
  $.validator.addMethod("dateBR", function(value, element) {
    return this.optional(element) || /^\d{2}\/\d{2}\/\d{4}$/.test(value);
  }, "Por favor, insira uma data válida no formato dd/mm/aaaa.");

  $.validator.addMethod("cpfBR", function(value, element) {
    value = value.replace(/[^\d]+/g,'');
    if(value.length !== 11) return false;

    var add, rev, i;

    // Valida o primeiro dígito verificador
    add = 0;
    for (i = 0; i < 9; i++) add += parseInt(value.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(value.charAt(9))) return false;

    // Valida o segundo dígito verificador
    add = 0;
    for (i = 0; i < 10; i++) add += parseInt(value.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(value.charAt(10))) return false;

    return true;
  }, "Por favor, insira um CPF válido.");
});