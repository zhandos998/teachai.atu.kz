import { useState } from "react";

export default function ChatInput({ sendMessage }) {
    const [text, setText] = useState("");

    const handleSend = () => {
        if (!text.trim()) return;

        sendMessage(text);
        setText("");
    };

    return (
        <div className="p-4 bg-white border-t flex items-center space-x-3">
            <input
                value={text}
                onChange={(e) => setText(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && handleSend()}
                placeholder="Напишите сообщение..."
                className="flex-1 border rounded-full px-4 py-2"
            />

            <button
                onClick={handleSend}
                className="bg-[#21397D] text-white px-4 py-2 rounded-full"
            >
                ➤
            </button>
        </div>
    );
}
