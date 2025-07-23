document.addEventListener('DOMContentLoaded', function() {
  const todoList = JSON.parse(localStorage.getItem('todoList')) || [];
  const baseTodoId = 'todoitem';
  const form = document.forms.toDoForm;
  const tasksContainer = document.getElementById('tasksContainer');
  const deleteSelectedBtn = document.getElementById('deleteSelected');
  const showCompletedCheckbox = document.getElementById('showCompleted');

  // Загрузка задач при открытии страницы
  renderTasks();

  // Обработка отправки формы
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    addToDo();
  });

  // Добавление новой задачи
  function addToDo() {
    const newTodo = {
      id: createNewId(),
      title: form.elements.title.value,
      date: form.elements.date.value,
      color: form.elements.color.value,
      description: form.elements.description.value,
      completed: false,
      createdAt: new Date().toISOString()
    };

    todoList.push(newTodo);
    saveToLocalStorage();
    renderTasks();
    form.reset();
    form.elements.color.value = "#5758A6"; // Возвращаем цвет по умолчанию
  }

  // Создание нового ID
  function createNewId() {
    return todoList.length === 0 ? 1 : Math.max(...todoList.map(todo => todo.id)) + 1;
  }

  // Удаление задачи
  function deleteElement(id) {
    const index = todoList.findIndex(item => item.id === id);
    if (index !== -1) {
      todoList.splice(index, 1);
      saveToLocalStorage();
      renderTasks();
    }
  }

  // Удаление выделенных задач
  deleteSelectedBtn.addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.task-checkbox:checked');
    const idsToDelete = Array.from(checkboxes).map(checkbox => parseInt(checkbox.dataset.id));
    
    idsToDelete.forEach(id => {
      const index = todoList.findIndex(item => item.id === id);
      if (index !== -1) {
        todoList.splice(index, 1);
      }
    });
    
    saveToLocalStorage();
    renderTasks();
    deleteSelectedBtn.disabled = true;
  });

  // Переключение статуса выполнения
  function toggleComplete(id) {
    const task = todoList.find(item => item.id === id);
    if (task) {
      task.completed = !task.completed;
      saveToLocalStorage();
      renderTasks();
    }
  }

  // Отображение задач
  function renderTasks() {
    tasksContainer.innerHTML = '';
    const showCompleted = showCompletedCheckbox.checked;
    
    const filteredTasks = showCompleted 
      ? todoList 
      : todoList.filter(task => !task.completed);
    
    filteredTasks.sort((a, b) => new Date(a.date) - new Date(b.date));
    
    if (filteredTasks.length === 0) {
      tasksContainer.innerHTML = '<p class="text-center">Нет задач</p>';
      return;
    }
    
    filteredTasks.forEach(task => {
      const div = document.createElement('div');
      div.id = baseTodoId + task.id;
      div.className = `row my-3 task-item ${task.completed ? 'completed' : ''}`;
      
      div.innerHTML = `
        <div class="col">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: ${task.color}">
              <span>${formatDate(task.date)}</span>
              <div class="form-check">
                <input class="form-check-input task-checkbox" type="checkbox" data-id="${task.id}">
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h5 class="card-title">${task.title}</h5>
                  <p class="card-text">${task.description || 'Нет описания'}</p>
                </div>
                <div>
                  <button type="button" class="btn btn-sm ${task.completed ? 'btn-warning' : 'btn-success'}" 
                          onclick="toggleComplete(${task.id})">
                    ${task.completed ? '<i class="fas fa-undo"></i> Возобновить' : '<i class="fas fa-check"></i> Выполнено'}
                  </button>
                </div>
              </div>
              <div class="d-flex justify-content-between mt-3">
                <small class="text-muted">Добавлено: ${formatDateTime(task.createdAt)}</small>
                <button type="button" class="btn btn-link text-danger" 
                        onclick="deleteElement(${task.id})">
                  <i class="fas fa-trash-alt"></i> Удалить
                </button>
              </div>
            </div>
          </div>
        </div>`;
      
      tasksContainer.appendChild(div);
    });
    
    // Обновляем состояние чекбоксов
    updateCheckboxes();
  }

  // Форматирование даты
  function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('ru-RU', options);
  }

  // Форматирование даты и времени
  function formatDateTime(dateTimeString) {
    const options = { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    return new Date(dateTimeString).toLocaleDateString('ru-RU', options);
  }

  // Сохранение в localStorage
  function saveToLocalStorage() {
    localStorage.setItem('todoList', JSON.stringify(todoList));
  }

  // Обновление состояния чекбоксов
  function updateCheckboxes() {
    const checkboxes = document.querySelectorAll('.task-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const anyChecked = document.querySelectorAll('.task-checkbox:checked').length > 0;
        deleteSelectedBtn.disabled = !anyChecked;
      });
    });
  }

  // Переключение отображения выполненных задач
  showCompletedCheckbox.addEventListener('change', renderTasks);

  // Делаем функции глобальными для использования в HTML
  window.deleteElement = deleteElement;
  window.toggleComplete = toggleComplete;
});