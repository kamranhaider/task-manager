<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .app-shell { max-width: 900px; }
        .task-card { border: 0; border-radius: 14px; }
        .task-title.completed { text-decoration: line-through; color: #6c757d; }
        .task-description { white-space: pre-wrap; overflow-wrap: anywhere; }
    </style>
</head>
<body>
<main class="container app-shell py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">FULL-STACK ASSESSMENT</p>
            <h1 class="h2 mb-1">Task Manager</h1>
            <p class="text-secondary mb-0">Keep your work organized, one task at a time.</p>
        </div>
        <span id="task-count" class="badge text-bg-primary rounded-pill px-3 py-2">0 tasks</span>
    </div>

    <div id="alert-area" aria-live="polite"></div>

    <section class="card task-card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 id="form-heading" class="h5 mb-3">Add a task</h2>
            <form id="task-form">
                <input type="hidden" id="task-id">
                <div class="mb-3">
                    <label for="title" class="form-label">Task title *</label>
                    <input id="title" name="title" class="form-control" maxlength="255" required placeholder="e.g. Finish API endpoints">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea id="description" name="description" class="form-control" rows="2" placeholder="Add a few details..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button id="submit-button" class="btn btn-primary" type="submit">Add task</button>
                    <button id="cancel-edit" class="btn btn-outline-secondary d-none" type="button">Cancel edit</button>
                </div>
            </form>
        </div>
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Your tasks</h2>
            <button id="refresh-button" class="btn btn-sm btn-outline-secondary" type="button">Refresh</button>
        </div>
        <div id="loading" class="text-secondary py-3">Loading tasks...</div>
        <div id="task-list" class="d-grid gap-3"></div>
        <div id="empty-state" class="text-center text-secondary py-5 d-none">
            <div class="fs-1 mb-2">✓</div>
            <p class="mb-0">No tasks yet. Add your first task above.</p>
        </div>
    </section>
</main>

<script>
const API_URL = '/api/tasks';
const form = document.getElementById('task-form');
const titleInput = document.getElementById('title');
const descriptionInput = document.getElementById('description');
const taskIdInput = document.getElementById('task-id');
const taskList = document.getElementById('task-list');
const loading = document.getElementById('loading');
const emptyState = document.getElementById('empty-state');
const count = document.getElementById('task-count');

function showAlert(message, type = 'success') {
    document.getElementById('alert-area').innerHTML = '';
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.setAttribute('role', 'alert');
    alert.textContent = message;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close';
    close.setAttribute('data-bs-dismiss', 'alert');
    close.setAttribute('aria-label', 'Close');
    alert.appendChild(close);
    document.getElementById('alert-area').appendChild(alert);
}

async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...(options.headers || {})
        }
    });

    const data = response.status === 204 ? null : await response.json().catch(() => null);

    if (!response.ok) {
        const message = data?.message || 'Something went wrong. Please try again.';
        throw new Error(message);
    }
    return data;
}

function makeButton(label, className, action, taskId) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = className;
    button.textContent = label;
    button.addEventListener('click', () => action(taskId));
    return button;
}

function renderTasks(tasks) {
    taskList.replaceChildren();
    loading.classList.add('d-none');
    emptyState.classList.toggle('d-none', tasks.length > 0);
    count.textContent = `${tasks.length} ${tasks.length === 1 ? 'task' : 'tasks'}`;

    tasks.forEach(task => {
        const card = document.createElement('article');
        card.className = 'card task-card shadow-sm';

        const body = document.createElement('div');
        body.className = 'card-body p-3 p-md-4';

        const top = document.createElement('div');
        top.className = 'd-flex align-items-start gap-3';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input mt-1';
        checkbox.checked = Boolean(task.is_completed);
        checkbox.setAttribute('aria-label', `Mark ${task.title} as completed`);
        checkbox.addEventListener('change', () => toggleTask(task));

        const content = document.createElement('div');
        content.className = 'flex-grow-1 min-w-0';

        const title = document.createElement('h3');
        title.className = `h6 task-title mb-1${task.is_completed ? ' completed' : ''}`;
        title.textContent = task.title;

        const description = document.createElement('p');
        description.className = 'task-description text-secondary small mb-0';
        description.textContent = task.description || '';

        const status = document.createElement('span');
        status.className = `badge mt-2 ${task.is_completed ? 'text-bg-success' : 'text-bg-light border text-dark'}`;
        status.textContent = task.is_completed ? 'Completed' : 'Pending';

        content.append(title, description, status);
        top.append(checkbox, content);

        const actions = document.createElement('div');
        actions.className = 'd-flex gap-2 mt-3 flex-wrap';
        actions.append(
            makeButton('Edit', 'btn btn-sm btn-outline-primary', editTask, task.id),
            makeButton('Delete', 'btn btn-sm btn-outline-danger', deleteTask, task.id)
        );

        body.append(top, actions);
        card.appendChild(body);
        taskList.appendChild(card);
    });
}

async function loadTasks() {
    loading.classList.remove('d-none');
    loading.textContent = 'Loading tasks...';
    try {
        const tasks = await apiRequest(API_URL);
        renderTasks(tasks);
    } catch (error) {
        loading.textContent = '';
        showAlert(error.message, 'danger');
    }
}

function resetForm() {
    form.reset();
    taskIdInput.value = '';
    document.getElementById('form-heading').textContent = 'Add a task';
    document.getElementById('submit-button').textContent = 'Add task';
    document.getElementById('cancel-edit').classList.add('d-none');
}

form.addEventListener('submit', async event => {
    event.preventDefault();
    const id = taskIdInput.value;
    const payload = {
        title: titleInput.value.trim(),
        description: descriptionInput.value.trim() || null
    };
    if (!payload.title) {
        showAlert('Please enter a task title.', 'warning');
        return;
    }

    try {
        await apiRequest(id ? `${API_URL}/${id}` : API_URL, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });
        showAlert(id ? 'Task updated successfully.' : 'Task added successfully.');
        resetForm();
        await loadTasks();
    } catch (error) {
        showAlert(error.message, 'danger');
    }
});

async function editTask(id) {
    try {
        const task = await apiRequest(`${API_URL}/${id}`);
        taskIdInput.value = task.id;
        titleInput.value = task.title;
        descriptionInput.value = task.description || '';
        document.getElementById('form-heading').textContent = 'Edit task';
        document.getElementById('submit-button').textContent = 'Save changes';
        document.getElementById('cancel-edit').classList.remove('d-none');
        titleInput.focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        showAlert(error.message, 'danger');
    }
}

async function toggleTask(task) {
    try {
        await apiRequest(`${API_URL}/${task.id}`, {
            method: 'PATCH',
            body: JSON.stringify({ is_completed: !task.is_completed })
        });
        await loadTasks();
    } catch (error) {
        showAlert(error.message, 'danger');
        await loadTasks();
    }
}

async function deleteTask(id) {
    if (!window.confirm('Are you sure you want to delete this task?')) return;
    try {
        await apiRequest(`${API_URL}/${id}`, { method: 'DELETE' });
        showAlert('Task deleted successfully.');
        await loadTasks();
    } catch (error) {
        showAlert(error.message, 'danger');
    }
}

document.getElementById('cancel-edit').addEventListener('click', resetForm);
document.getElementById('refresh-button').addEventListener('click', loadTasks);
loadTasks();
</script>
</body>
</html>
