interface FeedbackData {
  nombre: string;
  email: string;
  sugerencia: string;
  rating: number;
}

export const saveFeedback = async (feedbackData: FeedbackData) => {
  try {
    // Guardar el rating
    const ratingFormData = new FormData();
    ratingFormData.append('rating', feedbackData.rating.toString());
    
    const ratingResponse = await fetch('/save_rating.php', {
      method: 'POST',
      body: ratingFormData
    });

    const ratingResult = await ratingResponse.text();
    if (ratingResult !== 'success') {
      throw new Error('Error al guardar la calificación');
    }

    // Guardar la sugerencia
    const sugerenciaFormData = new FormData();
    sugerenciaFormData.append('nombre', feedbackData.nombre);
    sugerenciaFormData.append('email', feedbackData.email);
    sugerenciaFormData.append('sugerencia', feedbackData.sugerencia);

    const sugerenciaResponse = await fetch('/guardar_sugerencia.php', {
      method: 'POST',
      body: sugerenciaFormData
    });

    // La respuesta será manejada por el PHP que redirige con SweetAlert
    return { success: true };
  } catch (error) {
    console.error('Error al guardar el feedback:', error);
    return { success: false, error };
  }
}; 