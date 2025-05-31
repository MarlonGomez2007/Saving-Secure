import React, { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { MessageCircle, X, Send, Bot } from 'lucide-react';
import ChatBotParticles from './ChatBotParticles';
import { createPortal } from 'react-dom';

interface Message {
  content: string;
  type: 'user' | 'bot';
}

const ChatBot = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([]);
  const [inputValue, setInputValue] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const [isButtonHovered, setIsButtonHovered] = useState(false);
  const [conversationHistory, setConversationHistory] = useState<Array<{role: string, content: string}>>([]);
  const [userInfo, setUserInfo] = useState({
    hasUsedApp: false,
    financialGoals: [] as string[],
    mentionedTopics: new Set<string>(),
    lastInteraction: Date.now()
  });
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const chatContentRef = useRef<HTMLDivElement>(null);
  const chatWindowRef = useRef<HTMLDivElement>(null);

  // Expresiones colombianas y saludos
  const commonGreetings = [
    "hola", "buenos días", "buenas tardes", "buenas noches", 
    "saludos", "hey", "qué tal", "cómo estás", "hi", "hello",
    "buenas"
  ];

  const isJustGreeting = (message: string) => {
    message = message.toLowerCase().trim();
    return message.length < 20 && commonGreetings.some(greeting => 
      message === greeting || message.startsWith(greeting + " ") || message.endsWith(" " + greeting)
    );
  };

  const isFinancialQuery = (query: string) => {
    const financialKeywords = [
      "dinero", "plata", "lucas", "finanzas", "inversión", "invertir", "ahorro", "ahorrar",
      "presupuesto", "credito", "prestamo", "hipoteca", "interés", "intereses",
      "deuda", "deudas", "banco", "cuenta", "tarjeta", "impuestos", "impuesto",
      "seguro", "seguros", "jubilación", "pensión", "bolsa", "acciones", "bonos",
      "fondos", "dividendos", "capital", "activos", "pasivos", "gastos", "ingresos",
      "nómina", "sueldo", "prima", "cesantías", "arriendo", "servicios", "facturas",
      "mercado", "compras", "cuotas", "fiado", "gota a gota", "DIAN", "declaración",
      "calculadora", "app", "aplicación", "herramienta"
    ];

    query = query.toLowerCase();
    return financialKeywords.some(keyword => query.includes(keyword));
  };

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(() => {
    if (isOpen && messages.length === 0) {
      // Add welcome message
      setIsTyping(true);
      setTimeout(() => {
        setMessages([{
          type: 'bot',
          content: '¡Hola! 👋 Soy su Asesor Financiero Virtual de Saving Secure. ¿En qué puedo ayudarle con sus finanzas hoy?'
        }]);
        setIsTyping(false);
      }, 1000);
    }
  }, [isOpen]);

  useEffect(() => {
    scrollToBottom();
  }, [messages, isTyping]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (chatWindowRef.current && !chatWindowRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputValue.trim()) return;

    const userMessage = inputValue.trim();
    setMessages(prev => [...prev, { type: 'user', content: userMessage }]);
    setInputValue('');
    setIsTyping(true);

    // Actualizar tiempo de última interacción
    setUserInfo(prev => ({ ...prev, lastInteraction: Date.now() }));

    // Verificar si es solo un saludo
    if (isJustGreeting(userMessage)) {
      if (conversationHistory.length === 0) {
        setTimeout(() => {
          const response = '¡Hola! 👋 Soy su Asesor Financiero Virtual de Saving Secure. ¿En qué puedo ayudarle con sus finanzas hoy?';
          setMessages(prev => [...prev, { type: 'bot', content: response }]);
          setConversationHistory([
            { role: 'user', content: userMessage },
            { role: 'assistant', content: response }
          ]);
          setIsTyping(false);
        }, 1000);
      } else {
        setTimeout(() => {
          setMessages(prev => [...prev, { 
            type: 'bot', 
            content: '¿En qué más le puedo ayudar con sus finanzas?' 
          }]);
          setIsTyping(false);
        }, 1000);
      }
      return;
    }

    // Verificar si la consulta es financiera
    if (!isFinancialQuery(userMessage)) {
      setTimeout(() => {
        setMessages(prev => [...prev, { 
          type: 'bot', 
          content: 'Como asesor especializado exclusivamente en finanzas personales, mi enfoque se limita a temas financieros y nuestra calculadora de gastos. ¿Podría reformular su pregunta hacia algún aspecto financiero? Por ejemplo: ahorros, inversiones, presupuestos, deudas o metas financieras.' 
        }]);
        setIsTyping(false);
      }, 1000);
      return;
    }

    try {
      // Preparar el prompt del sistema
      const systemPrompt = `Eres un asesor financiero virtual profesional, experto EXCLUSIVAMENTE en finanzas personales, formal y respetuoso. 
      Tu objetivo es ayudar a los usuarios a gestionar sus finanzas personales, ahorrar dinero y tomar decisiones financieras informadas.
      
      IMPORTANTE:
      1. USA UN TONO FORMAL: Utiliza "usted" en lugar de "tú"
      2. SÉ CORDIAL Y PROFESIONAL: Responde siempre de manera educada
      3. MANTÉN RESPUESTAS BREVES: Máximo 4-5 líneas
      4. ENFÓCATE EN FINANZAS: Solo responde consultas financieras
      5. PERSONALIZA TUS RESPUESTAS: Adapta tus consejos al contexto del usuario
      
      ${userInfo.mentionedTopics.size > 0 ? `El usuario ha mostrado interés en: ${Array.from(userInfo.mentionedTopics).join(', ')}` : ''}`;

      // Llamada a la API
      const response = await fetch("https://openrouter.ai/api/v1/chat/completions", {
        method: "POST",
        headers: {
          "Authorization": "Bearer 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",
          "Content-Type": "application/json",
          "HTTP-Referer": window.location.origin,
          "X-Title": "Saving Secure"
        },
        body: JSON.stringify({
          model: "deepseek/deepseek-chat",
          messages: [
            { role: "system", content: systemPrompt },
            ...conversationHistory.slice(-5),
            { role: "user", content: userMessage }
          ],
          max_tokens: 500,
          temperature: 0.7
        })
      });

      if (!response.ok) {
        throw new Error(`Error ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      const botResponse = data.choices[0].message.content;

      // Actualizar mensajes y historial
      setMessages(prev => [...prev, { type: 'bot', content: botResponse }]);
      setConversationHistory(prev => [...prev, 
        { role: 'user', content: userMessage },
        { role: 'assistant', content: botResponse }
      ]);

      // Detectar temas financieros mencionados
      const topics = {
        ahorro: ["ahorro", "ahorrar", "guardar plata", "guardar dinero"],
        inversion: ["inversion", "invertir", "acciones", "bolsa", "CDT"],
        deuda: ["deuda", "credito", "prestamo", "hipoteca", "tarjeta"],
        presupuesto: ["presupuesto", "gastos", "ingresos", "sueldo", "nomina"],
        impuestos: ["impuestos", "DIAN", "declaración", "renta", "IVA"]
      };

      Object.entries(topics).forEach(([topic, keywords]) => {
        if (keywords.some(keyword => userMessage.toLowerCase().includes(keyword))) {
          setUserInfo(prev => ({
            ...prev,
            mentionedTopics: new Set([...prev.mentionedTopics, topic])
          }));
        }
      });

    } catch (error) {
      console.error('Error al llamar a la API:', error);
      setMessages(prev => [...prev, { 
        type: 'bot', 
        content: 'Lo siento, estoy experimentando problemas técnicos temporales. Por favor, intente su consulta nuevamente en unos momentos.' 
      }]);
    } finally {
      setIsTyping(false);
    }
  };

  const buttonVariants = {
    initial: { scale: 1 },
    hover: { 
      scale: 1.1,
      boxShadow: '0 0 25px rgba(254, 205, 2, 0.5)',
      transition: { duration: 0.3, ease: "easeInOut" }
    },
    tap: { scale: 0.9 }
  };

  const messageVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { 
      opacity: 1, 
      y: 0,
      transition: { duration: 0.3, ease: "easeOut" }
    },
    exit: { 
      opacity: 0, 
      y: 20,
      transition: { duration: 0.2 }
    }
  };

  return createPortal(
    <div className="relative" style={{ position: 'relative', zIndex: 9999 }}>
      {/* Chat Button */}
      <motion.button
        onClick={() => setIsOpen(true)}
        className="fixed bottom-6 right-6 bg-[#fecd02] text-black p-3 md:p-4 rounded-full shadow-lg hover:bg-yellow-400 transition-all duration-300 z-[9999] group"
        style={{ position: 'fixed' }}
        variants={buttonVariants}
        initial="initial"
        whileHover="hover"
        whileTap="tap"
        animate={{
          scale: [1, 1.2, 1, 1.2, 1],
          rotate: [0, -10, 10, -10, 0],
          boxShadow: [
            '0 0 0 rgba(254, 205, 2, 0)',
            '0 0 20px rgba(254, 205, 2, 0.7)',
            '0 0 0 rgba(254, 205, 2, 0)',
            '0 0 20px rgba(254, 205, 2, 0.7)',
            '0 0 0 rgba(254, 205, 2, 0)'
          ],
          transition: {
            duration: 2,
            repeat: Infinity,
            repeatDelay: 1,
            ease: "easeInOut"
          }
        }}
        onHoverStart={() => setIsButtonHovered(true)}
        onHoverEnd={() => setIsButtonHovered(false)}
      >
        <motion.div
          animate={{
            rotate: isButtonHovered ? [0, -10, 10, -10, 10, 0] : 0
          }}
          transition={{ duration: 0.5 }}
        >
          <MessageCircle className="w-6 h-6" />
        </motion.div>
        <motion.div
          initial={{ opacity: 0, scale: 0.5 }}
          animate={{ 
            opacity: isButtonHovered ? 1 : 0,
            scale: isButtonHovered ? 1 : 0.5
          }}
          className="absolute -top-10 right-0 bg-white text-black px-3 py-1 rounded-full text-sm whitespace-nowrap shadow-lg"
        >
          ¿Necesita ayuda? 💬
        </motion.div>
      </motion.button>

      {/* Chat Window */}
      <AnimatePresence>
        {isOpen && (
          <motion.div
            ref={chatWindowRef}
            initial={{ opacity: 0, y: 20, scale: 0.9 }}
            animate={{ 
              opacity: 1, 
              y: 0, 
              scale: 1,
              transition: {
                type: "spring",
                stiffness: 300,
                damping: 30
              }
            }}
            exit={{ 
              opacity: 0, 
              y: 20, 
              scale: 0.9,
              transition: { duration: 0.2 }
            }}
            className="fixed bottom-24 right-6 md:w-96 w-[calc(100%-3rem)] max-w-[400px] h-[600px] max-h-[80vh] rounded-2xl shadow-2xl overflow-hidden z-[9998] flex flex-col"
            style={{ background: 'rgba(255, 255, 255, 0.92)' }}
          >
            {/* Content container */}
            <div className="relative flex flex-col h-full" style={{ zIndex: 2 }}>
              {/* Header */}
              <div className="flex items-center justify-between p-3 md:p-4 border-b bg-white/70">
                <div className="flex items-center space-x-2 md:space-x-3">
                  <motion.div className="w-6 h-6 md:w-8 md:h-8 relative">
                    <img src="/assets/img/logo.png" alt="Saving Secure Logo" className="w-full h-full object-contain" />
                    <motion.div
                      className="absolute -bottom-1 -right-1 w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded-full"
                      animate={{
                        scale: [1, 1.2, 1],
                        opacity: [1, 0.8, 1]
                      }}
                      transition={{
                        duration: 2,
                        repeat: Infinity,
                        ease: "easeInOut"
                      }}
                    />
                  </motion.div>
                  <div>
                    <h3 className="text-gray-900 font-semibold text-sm md:text-base">Saving Secure</h3>
                    <p className="text-yellow-400 text-xs font-medium">Asesor Financiero Virtual</p>
                  </div>
                </div>
                <motion.button
                  onClick={() => setIsOpen(false)}
                  className="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full"
                  whileHover={{ rotate: 90 }}
                  transition={{ duration: 0.2 }}
                >
                  <X size={20} />
                </motion.button>
              </div>

              {/* Chat messages area with particles */}
              <div className="flex-1 relative">
                {/* Particles background */}
                <div className="absolute inset-0" style={{ zIndex: 1 }}>
                  <ChatBotParticles />
                </div>
                
                {/* Messages overlay */}
                <div
                  ref={chatContentRef}
                  className="relative h-full overflow-y-auto p-2 md:p-4 space-y-2 md:space-y-4 bg-transparent"
                  style={{ zIndex: 2 }}
                >
                  {messages.map((message, index) => (
                    <motion.div
                      key={index}
                      className={`flex ${message.type === 'user' ? 'justify-end' : 'justify-start'}`}
                      variants={messageVariants}
                      initial="hidden"
                      animate="visible"
                      exit="exit"
                    >
                      <div
                        className={`max-w-[85%] p-2 md:p-3 text-sm md:text-base rounded-2xl ${
                          message.type === 'user'
                            ? 'bg-[#fecd02] text-gray-900'
                            : 'bg-[rgb(31,32,41)] text-white'
                        } relative`}
                        style={{ zIndex: 3 }}
                      >
                        {message.content}
                      </div>
                    </motion.div>
                  ))}
                  {isTyping && (
                    <div className="flex justify-start" style={{ zIndex: 3 }}>
                      <div className="bg-[#1E1E1E] p-4 rounded-2xl">
                        <div className="flex space-x-2">
                          <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0s' }}></div>
                          <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0.2s' }}></div>
                          <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0.4s' }}></div>
                        </div>
                      </div>
                    </div>
                  )}
                  <div ref={messagesEndRef} />
                </div>
              </div>

              {/* Input form */}
              <form onSubmit={handleSubmit} className="p-2 md:p-4 border-t bg-white/70">
                <div className="relative flex items-center">
                  {/* Campo de entrada con efecto neomórfico */}
                  <div className="relative flex-1 mr-2">
                    <div className="absolute inset-0 bg-gradient-to-r from-[#fecd02]/20 to-yellow-500/20 rounded-xl blur-md transform scale-105" />
                    <div className="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-xl shadow-inner" />
                    <input
                      type="text"
                      value={inputValue}
                      onChange={(e) => setInputValue(e.target.value)}
                      placeholder="Escriba su mensaje..."
                      className="relative w-full bg-transparent text-gray-900 rounded-xl px-3 md:px-6 py-2 md:py-4 focus:outline-none placeholder:text-gray-400 text-sm md:text-base"
                    />
                    
                    {/* Efecto de onda al escribir */}
                    <motion.div
                      className="absolute bottom-0 left-0 right-0 h-[2px] bg-gradient-to-r from-[#fecd02] via-yellow-500 to-[#fecd02]"
                      initial={{ scaleX: 0 }}
                      animate={{ 
                        scaleX: inputValue ? [0, 1, 1] : 0,
                        x: inputValue ? ['0%', '100%', '0%'] : '0%'
                      }}
                      transition={{ 
                        duration: 2,
                        repeat: Infinity,
                        ease: "easeInOut"
                      }}
                    />
                  </div>

                  {/* Botón de enviar */}
                  <motion.button
                    type="submit"
                    disabled={!inputValue.trim()}
                    className="relative group"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                  >
                    <div className={`
                      relative p-3 rounded-full
                      ${!inputValue.trim()
                        ? 'bg-gray-100'
                        : 'bg-gradient-to-r from-[#fecd02] to-[#fecd02] shadow-md'
                      }
                      transition-all duration-300
                    `}>
                      <Send
                        size={22}
                        className={`transition-all duration-300 ${
                          inputValue.trim()
                            ? 'text-white stroke-[2]'
                            : 'text-gray-400'
                        }`}
                      />
                    </div>
                  </motion.button>
                </div>
              </form>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>,
    document.body
  );
};

export default ChatBot; 