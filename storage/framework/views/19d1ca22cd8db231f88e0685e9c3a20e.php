<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'CV Generator')); ?> - Crea tu CV con IA</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|space-grotesk:600,700,800&display=swap" rel="stylesheet" />
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
            color: #e2e8f0;
            min-height: 100vh;
        }
        
        /* ESTILOS DE IMPRESIÓN */
        @page {
            size: A4;
            margin: 0;
        }
        
        @media print {
            body { background: white !important; }
            #chatPanel, header, .no-print { display: none !important; }
            #cvPreview { 
                width: 100%;
                height: auto;
                overflow: visible;
                padding: 0;
                background: white !important;
            }
            .cv-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
            }
            #cvContent {
                box-shadow: none;
                border: none;
                padding: 2cm;
            }
        }
        
        /* ESTILOS DEL CV */
        .cv-document { font-family: 'Inter', sans-serif; font-size: 10.5pt; line-height: 1.65; color: #1a1a1a; }
        .cv-header { margin-bottom: 28px; padding-bottom: 20px; border-bottom: 3px solid #6366f1; }
        .cv-name { font-size: 32pt; font-weight: 800; color: #0f172a; margin-bottom: 10px; letter-spacing: -0.03em; line-height: 1.1; }
        .cv-contact { font-size: 10pt; color: #64748b; display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px; }
        .cv-contact-separator { color: #cbd5e1; }
        .cv-section-title { font-size: 13pt; font-weight: 800; text-transform: uppercase; color: #6366f1; margin-top: 28px; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; letter-spacing: 0.08em; }
        .cv-profile { font-size: 10.5pt; line-height: 1.75; color: #334155; text-align: justify; margin-bottom: 24px; }
        .cv-item { margin-bottom: 22px; page-break-inside: avoid; }
        .cv-item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 16px; }
        .cv-item-title { font-size: 11.5pt; font-weight: 700; color: #0f172a; line-height: 1.3; }
        .cv-item-subtitle { font-size: 10.5pt; font-weight: 600; color: #475569; margin-top: 3px; }
        .cv-item-date { font-size: 9.5pt; color: #6366f1; white-space: nowrap; font-weight: 600; background: #eef2ff; padding: 4px 12px; border-radius: 6px; }
        .cv-item-description { font-size: 10pt; line-height: 1.7; color: #475569; margin-top: 10px; text-align: justify; }
        .cv-skills-category { margin-bottom: 14px; font-size: 10pt; }
        .cv-skills-category-name { font-weight: 700; color: #0f172a; margin-right: 8px; }
        .cv-skills-list { color: #475569; line-height: 1.6; }
        .cv-bullet { color: #6366f1; margin-right: 10px; font-weight: 700; }

        /* ANIMACIONES */
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .animate-fade-in { animation: fadeIn 0.4s ease; }

        /* LAYOUT */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            min-height: calc(100vh - 120px);
        }

        /* HEADER */
        .header {
            background: rgba(20, 20, 30, 0.6);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
            padding: 1.5rem 2rem;
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }

        .logo-text h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text p {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* PANELS */
        .panel {
            background: rgba(30, 30, 50, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1.5rem;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .panel-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .chat-icon {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .cv-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .panel-title {
            flex: 1;
        }

        .panel-title h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .panel-title p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* MENSAJES */
        .messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 1.5rem;
            padding-right: 0.5rem;
        }

        .messages::-webkit-scrollbar { width: 6px; }
        .messages::-webkit-scrollbar-track { background: rgba(139, 92, 246, 0.1); border-radius: 3px; }
        .messages::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.3); border-radius: 3px; }

        .message {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
            animation: slideIn 0.3s ease;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bot-avatar {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
        }

        .user-avatar {
            background: rgba(148, 163, 184, 0.3);
        }

        .message-content {
            flex: 1;
            max-width: 80%;
        }

        .message-bubble {
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            line-height: 1.6;
        }

        .bot-bubble {
            background: rgba(51, 65, 85, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 1rem 1rem 1rem 0.25rem;
        }

        .user-bubble {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 1rem 1rem 0.25rem 1rem;
            margin-left: auto;
        }

        .user-message {
            flex-direction: row-reverse;
        }

        .user-message .message-content {
            display: flex;
            justify-content: flex-end;
        }

        /* TYPING INDICATOR */
        .typing {
            display: flex;
            gap: 0.4rem;
            padding: 1rem;
        }

        .typing span {
            width: 8px;
            height: 8px;
            background: #8b5cf6;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        /* FORMULARIO */
        .chat-form {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .chat-input {
            flex: 1;
            background: rgba(51, 65, 85, 0.5);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 1rem;
            padding: 1rem;
            color: #e2e8f0;
            font-size: 0.95rem;
            font-family: inherit;
            resize: none;
            transition: all 0.2s;
        }

        .chat-input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            border: none;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .btn-success:hover:not(:disabled) {
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-reset {
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-reset:hover {
            background: rgba(239, 68, 68, 0.2);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* CV PREVIEW */
        .cv-preview {
            background: white;
            color: #1e293b;
            border-radius: 1.5rem;
            padding: 3rem;
            overflow-y: auto;
        }

        .cv-preview::-webkit-scrollbar { width: 6px; }
        .cv-preview::-webkit-scrollbar-track { background: #f1f5f9; }
        .cv-preview::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            opacity: 0.3;
            animation: float 3s ease-in-out infinite;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .panel { min-height: 400px; }
            .status-badge { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header no-print">
        <div class="header-content">
            <div class="logo-area">
                <div class="logo-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1>CV Generator AI</h1>
                    <p>Powered by Artificial Intelligence</p>
                </div>
            </div>
            <div class="status-badge">
                <div class="status-dot"></div>
                <span style="font-size: 0.85rem; font-weight: 600; color: #10b981;">Sistema Activo</span>
            </div>
        </div>
    </header>

    <!-- Main -->
    <div class="container">
        <!-- Panel Chat -->
        <div id="chatPanel" class="panel">
            <div class="panel-header">
                <div class="panel-icon chat-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div class="panel-title">
                    <h2>Asistente IA</h2>
                    <p>Conversación guiada para crear tu CV</p>
                </div>
                <button onclick="resetChat()" class="btn btn-reset" title="Reiniciar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>

            <div class="messages" id="messagesContainer">
                <!-- Los mensajes se agregan aquí dinámicamente -->
            </div>

            <form id="chatForm" class="chat-form">
                <input 
                    type="text" 
                    id="userInput"
                    class="chat-input"
                    placeholder="Escribe tu respuesta aquí..."
                    autocomplete="off"
                    autofocus
                >
                <button type="submit" class="btn">
                    <span>Enviar</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Panel CV Preview -->
        <div class="panel" style="padding: 0; overflow: hidden;">
            <div class="panel-header no-print" style="padding: 2rem; margin: 0;">
                <div class="panel-icon cv-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="panel-title">
                    <h2>Vista Previa</h2>
                    <p>Tu CV generado con IA</p>
                </div>
                <button id="downloadBtn" class="btn btn-success" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Descargar PDF</span>
                </button>
            </div>

            <div id="cvPreview" class="cv-preview">
                <div id="emptyState" class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3>Tu CV aparecerá aquí</h3>
                    <p>Comienza la conversación para generar tu currículum profesional</p>
                </div>

                <div id="cvContent" class="cv-document hidden">
                    <div class="cv-header">
                        <div id="cvName" class="cv-name">NOMBRE COMPLETO</div>
                        <div id="cvContact" class="cv-contact"></div>
                    </div>

                    <div id="profileSection" class="hidden">
                        <div class="cv-section-title">Perfil Profesional</div>
                        <div id="cvProfile" class="cv-profile"></div>
                    </div>

                    <div id="experienceSection" class="hidden">
                        <div class="cv-section-title">Experiencia Laboral</div>
                        <div id="cvExperience"></div>
                    </div>

                    <div id="projectsSection" class="hidden">
                        <div class="cv-section-title">Proyectos Destacados</div>
                        <div id="cvProjects"></div>
                    </div>

                    <div id="educationSection" class="hidden">
                        <div class="cv-section-title">Educación</div>
                        <div id="cvEducation"></div>
                    </div>

                    <div id="skillsSection" class="hidden">
                        <div class="cv-section-title">Habilidades</div>
                        <div id="cvSkills"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/welcome.blade.php ENDPATH**/ ?>