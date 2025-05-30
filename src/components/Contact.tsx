import React, { useEffect, useRef, useState } from 'react';

const Contact = () => {
  const contactRef = useRef<HTMLDivElement>(null);
  const [isVisible, setIsVisible] = useState(false);
  const [rating, setRating] = useState(0);
  const [hoveredRating, setHoveredRating] = useState(0);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    company: '',
    message: ''
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsVisible(true);
        }
      },
      { threshold: 0.2 }
    );

    if (contactRef.current) {
      observer.observe(contactRef.current);
    }

    return () => observer.disconnect();
  }, []);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    // Simulate form submission
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    console.log('Form submitted:', formData);
    setIsSubmitting(false);
    setFormData({ name: '', email: '', company: '', message: '' });
    
    // You could add a toast notification here
  };

  return (
    <section ref={contactRef} className="py-20 px-6 relative">
      <div className="max-w-5xl mx-auto">
        <div className={`text-center mb-16 transition-all duration-1000 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
          <h2 className="text-5xl font-bold mb-6 text-white">
            Conecta con <span className="text-[#fecd02]">Nosotros</span>
          </h2>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            ¿Listo para transformar tu gestión financiera? Contáctanos y descubre todo lo que podemos hacer por ti
          </p>
        </div>

        {/* Opinion Section */}
        <div className="mb-16">
          <div className="bg-gray-800/30 rounded-2xl p-8 max-w-3xl mx-auto">
            <div className={`text-center transition-all duration-1000 ${isVisible ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
              <h2 className="text-4xl font-bold mb-6 text-[#fecd02]">
                TU OPINIÓN
              </h2>
              <p className="text-xl text-gray-300 mb-8">
                ¿Qué te ha parecido nuestra plataforma?
              </p>
              
              <div className="flex justify-center space-x-6">
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    className="transform transition-all duration-300 hover:scale-110 focus:outline-none"
                    onClick={() => setRating(star)}
                    onMouseEnter={() => setHoveredRating(star)}
                    onMouseLeave={() => setHoveredRating(0)}
                  >
                    <svg
                      className={`w-14 h-14 transition-colors duration-300 ${
                        star <= (hoveredRating || rating) ? 'text-[#fecd02]' : 'text-gray-600'
                      }`}
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                      />
                    </svg>
                  </button>
                ))}
              </div>
              
              {rating > 0 && (
                <p className="mt-6 text-xl text-[#fecd02] font-semibold animate-fade-in-up">
                  ¡Gracias por tu valoración!
                </p>
              )}
            </div>
          </div>
        </div>

        <div className="grid lg:grid-cols-2 gap-12 items-start mt-16">
          {/* Contact Info */}
          <div className={`transition-all duration-1000 ${isVisible ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'}`} style={{ transitionDelay: '200ms' }}>
            <h3 className="text-3xl font-bold mb-8 text-white">Información de Contacto</h3>
            
            <div className="space-y-6">
              <div className="flex items-start space-x-4 group">
                <div className="w-12 h-12 bg-[#fecd02]/20 rounded-lg flex items-center justify-center group-hover:bg-[#fecd02]/30 transition-colors duration-300">
                  <div className="w-6 h-6 bg-[#fecd02] rounded-full"></div>
                </div>
                <div>
                  <h4 className="text-white font-semibold mb-1">Oficina Principal</h4>
                  <p className="text-gray-300">Bogotá, Colombia</p>
                  <p className="text-gray-300">Carrera 11 #93-20, Oficina 501</p>
                </div>
              </div>
              
              <div className="flex items-start space-x-4 group">
                <div className="w-12 h-12 bg-[#fecd02]/20 rounded-lg flex items-center justify-center group-hover:bg-[#fecd02]/30 transition-colors duration-300">
                  <div className="w-6 h-6 bg-[#fecd02] rounded-full"></div>
                </div>
                <div>
                  <h4 className="text-white font-semibold mb-1">Email</h4>
                  <p className="text-gray-300">hola@savingsecure.co</p>
                  <p className="text-gray-300">soporte@savingsecure.co</p>
                </div>
              </div>
              
              <div className="flex items-start space-x-4 group">
                <div className="w-12 h-12 bg-[#fecd02]/20 rounded-lg flex items-center justify-center group-hover:bg-[#fecd02]/30 transition-colors duration-300">
                  <div className="w-6 h-6 bg-[#fecd02] rounded-full"></div>
                </div>
                <div>
                  <h4 className="text-white font-semibold mb-1">Teléfono</h4>
                  <p className="text-gray-300">+57 (1) 234-5678</p>
                  <p className="text-gray-300">WhatsApp: +57 300 123 4567</p>
                </div>
              </div>
            </div>
          </div>

          {/* Contact Form */}
          <div className={`transition-all duration-1000 ${isVisible ? 'translate-x-0 opacity-100' : 'translate-x-10 opacity-0'}`} style={{ transitionDelay: '400ms' }}>
            <form onSubmit={handleSubmit} className="bg-gradient-to-br from-gray-800/30 to-gray-900/30 p-8 rounded-2xl backdrop-blur-sm border border-gray-700/30 hover:border-[#fecd02]/30 transition-all duration-500">
              <h3 className="text-2xl font-bold mb-6 text-white">Envíanos un Mensaje</h3>
              
              <div className="space-y-6">
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-gray-300 mb-2 font-medium">Nombre *</label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleInputChange}
                      required
                      className="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300"
                      placeholder="Tu nombre completo"
                    />
                  </div>
                  <div>
                    <label className="block text-gray-300 mb-2 font-medium">Email *</label>
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                      required
                      className="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300"
                      placeholder="tu@email.com"
                    />
                  </div>
                </div>
                
                <div>
                  <label className="block text-gray-300 mb-2 font-medium">Empresa</label>
                  <input
                    type="text"
                    name="company"
                    value={formData.company}
                    onChange={handleInputChange}
                    className="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300"
                    placeholder="Nombre de tu empresa (opcional)"
                  />
                </div>
                
                <div>
                  <label className="block text-gray-300 mb-2 font-medium">Mensaje *</label>
                  <textarea
                    name="message"
                    value={formData.message}
                    onChange={handleInputChange}
                    required
                    rows={4}
                    className="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300 resize-none"
                    placeholder="Cuéntanos cómo podemos ayudarte..."
                  />
                </div>
                
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className={`w-full py-4 rounded-lg font-semibold text-lg transition-all duration-300 ${
                    isSubmitting
                      ? 'bg-gray-600 text-gray-400 cursor-not-allowed'
                      : 'bg-[#fecd02] text-black hover:bg-yellow-400 hover:scale-105 hover:shadow-2xl hover:shadow-[#fecd02]/25'
                  }`}
                >
                  {isSubmitting ? 'Enviando...' : 'Enviar Mensaje'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Contact;
