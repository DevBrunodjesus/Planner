<?php
require 'conexao.php';
requerLogin();

$data_hoje = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM eventos WHERE usuario_id = ? AND data_evento = ? ORDER BY hora");
$stmt->execute([$_SESSION['usuario_id'], $data_hoje]);
$eventos = $stmt->fetchAll();

// Processar exclusão de evento
if (isset($_GET['excluir'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM eventos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$_GET['excluir'], $_SESSION['usuario_id']]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?excluido=1");
        exit();
    } catch(PDOException $e) {
        $erro = "❌ Erro ao excluir: " . $e->getMessage();
    }
}

// Processar formulário de adição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO eventos (usuario_id, titulo, descricao, data_evento, hora, tipo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['usuario_id'], $_POST['titulo'], $_POST['descricao'] ?? '', $_POST['data'], $_POST['hora'] ?? '09:00', $_POST['tipo'] ?? 'evento']);
        
        // Redireciona para evitar repost
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
        
    } catch(PDOException $e) {
        $erro = "❌ Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planner+</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
       
        <div class="panel-left">
            
           <img src="logo.png" alt="Logo Planner" class="logo-img" onerror="this.style.display='none'">
            
            <h3>➕ Adicionar Evento</h3>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="message success">✅ Evento salvo!</div>
            <?php endif; ?>
            <?php if (isset($_GET['excluido']) && $_GET['excluido'] == 1): ?>
                <div class="message success">✅ Evento excluído!</div>
            <?php endif; ?>
            <?php if (isset($erro)): ?><div class="message error"><?= $erro ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Título do Evento</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Reunião com equipe" required>
                </div>
                <div class="form-group">
                    <label>Descrição (opcional)</label>
                    <textarea name="descricao" class="form-control" placeholder="Detalhes..." rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" name="hora" class="form-control" value="09:00" required>
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="evento">📅 Evento</option>
                        <option value="lembrete">🔔 Lembrete</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">💾 Salvar Evento</button>
            </form>
            
            <div class="current-date">📅 <?= date('d/m/Y') ?></div>
        </div>
        
        <!-- PAINEL DIREITO -->
        <div class="panel-right">
            <div class="planner-header">
                <div class="logo-header">
                    <div class=".planner-header h1">
                     <h1>Planner+</h1>
                    </div>
                </div> 
                <div class="user-info">
                    <span>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                    <a href="logout.php" class="btn-logout">Sair</a>
                </div>
            </div>
            
            <h3>Horários do Dia</h3>
            
            <div class="planner-grid">
                <div class="time-column">
                    <?php for ($h = 8; $h <= 20; $h++): ?>
                        <div class="time-slot"><?= sprintf('%02d:00', $h) ?></div>
                    <?php endfor; ?>
                </div>
                <div class="events-column">
                    <?php for ($h = 8; $h <= 20; $h++):
                        $eventos_hora = array_filter($eventos, fn($e) => (int)substr($e['hora'], 0, 2) == $h);
                    ?>
                        <div class="event-slot">
                            <?php if (!empty($eventos_hora)): 
                                foreach ($eventos_hora as $evento): ?>
                                    <div class="event-block <?= $evento['tipo'] == 'lembrete' ? 'event-type-reminder' : 'event-type-event' ?>">
                                        <div class="event-header">
                                            <div class="event-title"><?= htmlspecialchars($evento['titulo']) ?></div>
                                            <a href="?excluir=<?= $evento['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este evento?')">🗑️</a>
                                        </div>
                                        <div class="event-details">
                                            <span><?= $evento['tipo'] == 'lembrete' ? '🔔' : '📅' ?> <?= substr($evento['hora'], 0, 5) ?></span>
                                            <?php if ($evento['descricao']): ?><span title="<?= htmlspecialchars($evento['descricao']) ?>">📝</span><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; 
                            else: ?>
                                <div class="no-event">—</div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <?php if (empty($eventos)): ?>
                <div class="empty-state">
                    <h4>✨ Dia Livre!</h4>
                    <p>Não há eventos agendados para hoje.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Confirmação adicional para exclusão
        document.querySelectorAll('.btn-excluir').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir este evento?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>