// Forms Clipboard

$(document).ready(() => {
  const clipboard = new ClipboardJS(".clipboard-trigger");

  // Exibe mensagens de sucesso quando o link for copiado
  clipboard.on('success', (e) => {
    toastr.success('Link copiado com sucesso', 'Sucesso', {
      closeButton: true,
      progressBar: true,
      timeOut: 3000
    });

    // Opcional: limpar a seleção de texto após a cópia
    e.clearSelection();
  });

  // Tratar erros de cópia, se necessário
  clipboard.on('error', (e) => {
    toastr.error('Falha ao copiar o link', 'Erro', {
      closeButton: true,
      progressBar: true,
      timeOut: 3000
    });
  });
});
