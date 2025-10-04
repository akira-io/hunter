import ChatLayout from '@/layouts/chat-layout';
import { Head } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';

export default function ChatIndex() {
    return (
        <ChatLayout title="Messages" showSidebar={true} conversationId={null}>
            <Head title="Messages" />

            {/* Empty State - No Conversation Selected */}
            <div className="flex h-full flex-col items-center justify-center p-8 text-center">
                <div className="mb-6 grid size-20 place-items-center rounded-full bg-gradient-to-br from-purple-100 to-purple-200 dark:from-purple-900/20 dark:to-purple-800/20">
                    <MessageCircle size={40} className="text-purple-600 dark:text-purple-400" />
                </div>
                <h2 className="mb-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Suas Mensagens</h2>
                <p className="mb-4 max-w-md text-zinc-600 dark:text-zinc-400">
                    Selecione uma conversa no sidebar para começar a conversar, ou inicie uma nova conversa com alguém.
                </p>
                <div className="mt-8 rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        💡 <strong>Tip: </strong> Clique em qualquer conversa na barra lateral para abri-la e começar a enviar mensagens.
                    </p>
                </div>
            </div>
        </ChatLayout>
    );
}
