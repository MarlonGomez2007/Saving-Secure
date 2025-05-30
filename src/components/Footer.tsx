import React from 'react';
import { Facebook, Instagram, Linkedin, Youtube } from 'lucide-react';
import ChatBotParticles from './ChatBotParticles';

const Footer = () => {
  const socialLinks = [
    { Icon: Instagram, href: 'https://www.instagram.com/saving._.secure?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==', label: 'Instagram' },
    

  ];

  const handleDocumentClick = (documentPath: string) => {
    const baseUrl = window.location.origin;
    window.open(`${baseUrl}${documentPath}`, '_blank');
  };

  return (
    <footer className="relative bg-black/50 backdrop-blur-sm border-t border-gray-800/50 py-16 px-6 overflow-hidden">
      <ChatBotParticles />
      <div className="relative z-10 max-w-7xl mx-auto">
        <div className="grid lg:grid-cols-4 md:grid-cols-2 gap-8 mb-12">
          {/* Brand */}
          <div className="lg:col-span-2">
            <div className="flex items-center gap-4 mb-4">
              <img src="/assets/img/logo.png" alt="Saving Secure Logo" className="w-16 h-16 object-contain" />
              <h3 className="text-3xl font-bold text-white">
                Saving <span className="text-[#fecd02]">Secure</span>
              </h3>
            </div>
            <p className="text-gray-300 mb-6 max-w-md leading-relaxed">
              La fintech colombiana que revoluciona la gestión financiera personal y empresarial 
              con tecnología de vanguardia y la máxima seguridad.
            </p>
            
            {/* Social Links */}
            <div className="flex space-x-4">
              {socialLinks.map(({ Icon, href, label }) => (
                <a
                  key={label}
                  href={href}
                  className="w-12 h-12 bg-gray-800/50 rounded-full flex items-center justify-center text-gray-400 hover:text-[#fecd02] hover:bg-[#fecd02]/10 hover:scale-110 transition-all duration-300"
                  aria-label={label}
                >
                  <Icon size={20} />
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="text-white font-semibold mb-4 text-lg">Enlaces Rápidos</h4>
            <ul className="space-y-3">
              {['Inicio', 'Sobre Nosotros', 'Servicios', 'Casos de Uso', 'Blog', 'Contacto'].map((link) => (
                <li key={link}>
                  <a href="#" className="text-gray-300 hover:text-[#fecd02] transition-colors duration-300 hover:translate-x-1 inline-block">
                    {link}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Legal */}
          <div>
            <h4 className="text-white font-semibold mb-4 text-lg">Legal</h4>
            <ul className="space-y-3">
              {[
                { text: 'Términos y Condiciones', path: '/documentos/terminos de usos.pdf' },
                { text: 'Política de Privacidad', path: '/documentos/Politica de Privacidad Saving Secure.pdf' },
                { text: 'Política de Cookies', path: '/documentos/cookies.pdf' },
              ].map((link) => (
                <li key={link.text}>
                  <button
                    onClick={() => handleDocumentClick(link.path)}
                    className="text-gray-300 hover:text-[#fecd02] transition-colors duration-300 hover:translate-x-1 inline-block cursor-pointer bg-transparent border-0"
                  >
                    {link.text}
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="pt-8 border-t border-gray-800/50">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-gray-400 mb-4 md:mb-0">
              © 2024 Saving Secure. Todos los derechos reservados.
            </p>
            <div className="flex items-center space-x-6">
              <span className="text-gray-400 text-sm">Supervisado por</span>
              <div className="w-8 h-8 bg-[#fecd02] rounded flex items-center justify-center">
                <span className="text-black font-bold text-xs">SFC</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
