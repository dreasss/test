document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const primary = body.dataset.primary;
    const secondary = body.dataset.secondary;
    if (primary) {
        document.documentElement.style.setProperty('--primary', primary);
    }
    if (secondary) {
        document.documentElement.style.setProperty('--secondary', secondary);
    }

    const themeToggle = document.querySelector('[data-theme-toggle]');
    const storedTheme = localStorage.getItem('sd_theme');
    if (storedTheme === 'dark') {
        body.classList.add('dark');
    }
    themeToggle?.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('sd_theme', body.classList.contains('dark') ? 'dark' : 'light');
    });

    const slogan = document.querySelector('.brand-slogan');
    if (slogan) {
        const texts = [slogan.dataset.sloganRu, slogan.dataset.sloganEn].filter(Boolean);
        let idx = 0;
        let char = 0;
        const type = () => {
            const text = texts[idx] || '';
            slogan.textContent = text.slice(0, char++);
            if (char <= text.length) {
                setTimeout(type, 50);
            } else {
                setTimeout(() => {
                    slogan.classList.add('fade');
                    setTimeout(() => {
                        slogan.classList.remove('fade');
                        char = 0;
                        idx = (idx + 1) % texts.length;
                        type();
                    }, 600);
                }, 2000);
            }
        };
        type();
    }

    const typeahead = document.getElementById('typeahead');
    const suggestions = document.getElementById('suggestions');
    if (typeahead && suggestions) {
        let timer;
        typeahead.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                if (!typeahead.value.trim()) {
                    suggestions.innerHTML = '';
                    return;
                }
                const res = await fetch(`/api/suggestions?q=${encodeURIComponent(typeahead.value)}`);
                const data = await res.json();
                suggestions.innerHTML = '';
                (data.phrases || []).forEach((phrase) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = phrase;
                    btn.addEventListener('click', () => {
                        const start = typeahead.selectionStart;
                        const end = typeahead.selectionEnd;
                        const value = typeahead.value;
                        typeahead.value = value.substring(0, start) + phrase + value.substring(end);
                        typeahead.focus();
                    });
                    suggestions.appendChild(btn);
                });
            }, 300);
        });
    }

    const priorityGroup = document.querySelector('[data-priority-group]');
    if (priorityGroup) {
        const input = priorityGroup.querySelector('[data-priority-input]');
        priorityGroup.querySelectorAll('[data-priority]').forEach((btn) => {
            btn.addEventListener('click', () => {
                priorityGroup.querySelectorAll('.priority').forEach((el) => el.classList.remove('active'));
                btn.classList.add('active');
                input.value = btn.dataset.priority;
            });
        });
    }

    const editProfile = document.querySelector('[data-edit-profile]');
    if (editProfile) {
        editProfile.addEventListener('click', () => {
            document.querySelectorAll('[data-profile-input]').forEach((input) => {
                input.removeAttribute('readonly');
            });
        });
    }

    document.querySelectorAll('.toolbar button').forEach((button) => {
        button.addEventListener('click', () => {
            document.execCommand(button.dataset.cmd, false, null);
        });
    });
});

function syncEditor() {
    document.querySelectorAll('[data-editor-target]').forEach((textarea) => {
        const target = textarea.dataset.editorTarget;
        const editor = document.querySelector(`[data-target="${target}"]`);
        if (editor) {
            textarea.value = editor.innerHTML;
        }
    });
}
