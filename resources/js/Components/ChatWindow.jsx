import { useRef, useEffect } from "react";

export default function ChatWindow({ messages }) {
    const endRef = useRef(null);

    useEffect(() => {
        endRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    return (
        <div className="flex-1 overflow-y-auto p-6 space-y-4 bg-[#f7f8fc]">
            {messages.map((msg, i) => (
                <div
                    key={i}
                    className={msg.role === "user" ? "text-right" : "text-left"}
                >
                    <div
                        className={
                            "inline-block px-4 py-2 rounded-xl max-w-[70%] " +
                            (msg.role === "user"
                                ? "bg-[#21397D] text-white"
                                : "bg-gray-200 text-gray-900")
                        }
                    >
                        {msg.content}
                    </div>
                </div>
            ))}

            <div ref={endRef}></div>
        </div>
    );
}
