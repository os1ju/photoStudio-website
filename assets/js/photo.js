const initSlider = () => {
    const DEFAULT_SPEED = 0.5;

    const slider = document.querySelector(".slider");
    if (!slider) return;

    const wrapper = document.querySelector(".slider-track");

    wrapper.innerHTML += wrapper.innerHTML;

    let speed = DEFAULT_SPEED;
    let position = 0;

    slider.addEventListener("mouseenter", () => {
      speed = DEFAULT_SPEED / 2;
    });

    slider.addEventListener("mouseleave", () => {
      speed = DEFAULT_SPEED;
    });

    function animate() {
      position -= speed;

      if (Math.abs(position) >= wrapper.scrollWidth / 2) {
        position = 0;
      }

      wrapper.style.transform = `translateX(${position}px)`;
      requestAnimationFrame(animate);
    }

    animate();
  }

  initSlider();


 document.getElementById('btn-register').addEventListener('click', function() {
        const username = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;
        const confirmPassword = document.getElementById('reg-password2').value;
        const phone = document.getElementById('reg-phone').value;
        const gender = document.querySelector('input[name="gender"]:checked');
        const birthDate = document.getElementById('reg-birthdate').value;
        
        fetch('api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                email: email,
                password: password,
                confirm_password: confirmPassword,
                phone: phone,
                gender: gender ? gender.value : null,
                birth_date: birthDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'profile.php';
            } else {
                const errorDiv = document.getElementById('error-messages');
                errorDiv.innerHTML = data.errors.join('<br>');
            }
        });
    });

    document.getElementById('btn-login').addEventListener('click', function() {
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        fetch('api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'profile.php';
            } else {
                const errorDiv = document.getElementById('error-messages');
                errorDiv.innerHTML = data.errors.join('<br>');
            }
        });
    });

    fetch('api/get-user.php')
    .then(response => response.json())
    .then(data => {
        if (data.role === 'admin') {
            document.getElementById('admin-menu').style.display = 'block';
        }
    });

const phoneInput = document.getElementById('reg-phone');

    phoneInput.addEventListener('input', () => {
        let value = phoneInput.value.replace(/\D/g, ''); // Удаляем всё, что не цифра
        let result = '+';

        if (value.length > 0) {
            result += value[0]; // код страны
        }
        if (value.length > 1) {
            result += ' (' + value.substring(1, 4);
        }
        if (value.length >= 4) {
            result += ') ' + value.substring(4, 7);
        }
        if (value.length >= 7) {
            result += ' ' + value.substring(7, 9);
        }
        if (value.length >= 9) {
            result += ' ' + value.substring(9, 11);
        }

        phoneInput.value = result;
    });