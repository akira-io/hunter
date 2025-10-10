import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Search, Smile } from 'lucide-react';
import { useState } from 'react';

interface EmojiPickerProps {
    onInsert: (emoji: string) => void;
}

const emojiCategories = {
    'Rostos Felizes': [
        { emoji: '😀', name: 'sorriso feliz' },
        { emoji: '😃', name: 'sorriso grande' },
        { emoji: '😄', name: 'sorriso olhos' },
        { emoji: '😁', name: 'sorriso dentes' },
        { emoji: '😆', name: 'rindo' },
        { emoji: '😅', name: 'suor' },
        { emoji: '🤣', name: 'gargalhada' },
        { emoji: '😂', name: 'chorando rindo' },
        { emoji: '🙂', name: 'leve sorriso' },
        { emoji: '🙃', name: 'invertido' },
        { emoji: '😉', name: 'piscada' },
        { emoji: '😊', name: 'feliz' },
        { emoji: '😇', name: 'anjo' },
    ],
    Amor: [
        { emoji: '🥰', name: 'apaixonado' },
        { emoji: '😍', name: 'amor' },
        { emoji: '🤩', name: 'estrelas' },
        { emoji: '😘', name: 'beijo' },
        { emoji: '😗', name: 'beijinho' },
        { emoji: '😚', name: 'beijo olhos' },
        { emoji: '😙', name: 'beijo sorriso' },
        { emoji: '❤️', name: 'coracao vermelho' },
        { emoji: '🧡', name: 'coracao laranja' },
        { emoji: '💛', name: 'coracao amarelo' },
        { emoji: '💚', name: 'coracao verde' },
        { emoji: '💙', name: 'coracao azul' },
        { emoji: '💜', name: 'coracao roxo' },
        { emoji: '💔', name: 'coracao partido' },
        { emoji: '💕', name: 'dois coracoes' },
    ],
    Expressões: [
        { emoji: '🤔', name: 'pensando' },
        { emoji: '🤨', name: 'sobrancelha' },
        { emoji: '😐', name: 'neutro' },
        { emoji: '😑', name: 'sem expressao' },
        { emoji: '😏', name: 'maroto' },
        { emoji: '😒', name: 'entediado' },
        { emoji: '🙄', name: 'revirando olhos' },
        { emoji: '😬', name: 'careta' },
        { emoji: '🤥', name: 'mentira' },
        { emoji: '😌', name: 'aliviado' },
        { emoji: '😔', name: 'pensativo' },
        { emoji: '😪', name: 'sonolento' },
        { emoji: '😴', name: 'dormindo' },
    ],
    'Rostos Negativos': [
        { emoji: '😕', name: 'confuso' },
        { emoji: '😟', name: 'preocupado' },
        { emoji: '🙁', name: 'triste leve' },
        { emoji: '☹️', name: 'muito triste' },
        { emoji: '😮', name: 'surpreso' },
        { emoji: '😯', name: 'boca aberta' },
        { emoji: '😲', name: 'chocado' },
        { emoji: '😳', name: 'corado' },
        { emoji: '🥺', name: 'implorando' },
        { emoji: '😢', name: 'chorando' },
        { emoji: '😭', name: 'chorando muito' },
        { emoji: '😱', name: 'gritando medo' },
        { emoji: '😖', name: 'confuso triste' },
        { emoji: '😞', name: 'desapontado' },
        { emoji: '😤', name: 'fumegando' },
        { emoji: '😡', name: 'bravo' },
        { emoji: '😠', name: 'zangado' },
    ],
    Mãos: [
        { emoji: '👋', name: 'acenando' },
        { emoji: '🤚', name: 'mao levantada' },
        { emoji: '✋', name: 'mao' },
        { emoji: '👌', name: 'ok' },
        { emoji: '✌️', name: 'vitoria' },
        { emoji: '🤞', name: 'dedos cruzados' },
        { emoji: '🤟', name: 'amo voce' },
        { emoji: '🤘', name: 'rock' },
        { emoji: '🤙', name: 'me liga' },
        { emoji: '👈', name: 'apontando esquerda' },
        { emoji: '👉', name: 'apontando direita' },
        { emoji: '👆', name: 'apontando cima' },
        { emoji: '👇', name: 'apontando baixo' },
        { emoji: '👍', name: 'joinha' },
        { emoji: '👎', name: 'joinha baixo' },
        { emoji: '✊', name: 'punho' },
        { emoji: '👊', name: 'punho batendo' },
        { emoji: '👏', name: 'aplaudindo' },
        { emoji: '🙌', name: 'maos levantadas' },
        { emoji: '🤝', name: 'aperto mao' },
        { emoji: '🙏', name: 'rezando' },
        { emoji: '💪', name: 'musculo' },
    ],
    Símbolos: [
        { emoji: '🔥', name: 'fogo' },
        { emoji: '⭐', name: 'estrela' },
        { emoji: '🌟', name: 'estrela brilhante' },
        { emoji: '✨', name: 'brilhos' },
        { emoji: '💫', name: 'tontura' },
        { emoji: '⚡', name: 'raio' },
        { emoji: '💥', name: 'explosao' },
        { emoji: '🌈', name: 'arco iris' },
        { emoji: '☀️', name: 'sol' },
        { emoji: '🌙', name: 'lua' },
        { emoji: '🌊', name: 'onda' },
        { emoji: '💧', name: 'gota' },
    ],
};

const allEmojis = Object.values(emojiCategories).flat();

export function EmojiPicker({ onInsert }: EmojiPickerProps) {
    const [search, setSearch] = useState('');
    const [open, setOpen] = useState(false);

    const filteredEmojis = search
        ? allEmojis.filter(
              (e) =>
                  e.name.toLowerCase().includes(search.toLowerCase()) ||
                  e.emoji.includes(search),
          )
        : null;

    const handleInsert = (emoji: string) => {
        onInsert(emoji);
        setOpen(false);
        setSearch('');
    };
    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground hover:text-foreground"
                >
                    <Smile className="h-4 w-4" />
                    <span className="ml-1 hidden sm:inline">Emoji</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent
                className="w-[280px] sm:w-[340px]"
                align="end"
                sideOffset={8}
            >
                <div className="flex flex-col space-y-3">
                    <div>
                        <h4 className="text-sm font-semibold text-foreground">
                            Escolha um Emoji
                        </h4>
                        <div className="relative mt-2">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Pesquisar emoji..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full rounded-md border border-border bg-muted py-2 pr-3 pl-9 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                            />
                        </div>
                    </div>
                    <div className="max-h-[300px] overflow-y-auto pr-1">
                        {filteredEmojis ? (
                            <div className="grid grid-cols-8 gap-1">
                                {filteredEmojis.map((item, i) => (
                                    <button
                                        key={i}
                                        type="button"
                                        onClick={() => handleInsert(item.emoji)}
                                        className="flex h-9 w-9 items-center justify-center rounded-md text-xl transition-colors hover:bg-accent"
                                        title={item.name}
                                    >
                                        {item.emoji}
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {Object.entries(emojiCategories).map(
                                    ([category, emojis]) => (
                                        <div key={category}>
                                            <p className="mb-2 text-xs font-medium text-muted-foreground">
                                                {category}
                                            </p>
                                            <div className="grid grid-cols-8 gap-1">
                                                {emojis.map((item, i) => (
                                                    <button
                                                        key={i}
                                                        type="button"
                                                        onClick={() =>
                                                            handleInsert(
                                                                item.emoji,
                                                            )
                                                        }
                                                        className="flex h-9 w-9 items-center justify-center rounded-md text-xl transition-colors hover:bg-accent"
                                                        title={item.name}
                                                    >
                                                        {item.emoji}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    ),
                                )}
                            </div>
                        )}
                        {filteredEmojis && filteredEmojis.length === 0 && (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                Nenhum emoji encontrado
                            </p>
                        )}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}
